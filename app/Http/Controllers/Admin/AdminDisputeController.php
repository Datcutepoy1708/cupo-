<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\SellerBalanceLog;
use App\Models\SellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDisputeController extends Controller
{
    /**
     * Danh sach tranh chap cho Admin.
     * - Browser -> Blade view (admin.disputes.index)
     * - AJAX/JSON -> JSON paginate kem meta dem tung trang thai
     *
     * Query params: status, search (order_number | buyer email), page
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.disputes.index');
        }

        $status = $request->query('status');
        $keyword = $request->query('search');

        $disputes = Dispute::with([
            'buyer',
            'sellerOrder.order',
            'sellerOrder.seller.sellerProfile',
        ])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->whereHas('buyer', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%'))
                    ->orWhereHas('sellerOrder.order', fn ($oq) => $oq->where('order_number', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Dispute::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($disputes->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_pending' => $counts->get('pending', 0),
                'total_in_progress' => $counts->get('in_progress', 0),
                'total_refunded' => $counts->get('refunded', 0),
                'total_rejected' => $counts->get('rejected', 0),
            ],
        ]));
    }

    /**
     * Chi tiet 1 tranh chap.
     * URL: GET /admin/disputes/{dispute}
     */
    public function show(Dispute $dispute): View
    {
        $dispute->load([
            'buyer',
            'sellerOrder.order.user',
            'sellerOrder.seller.sellerProfile',
            'sellerOrder.items.product',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Tiep nhan xu ly tranh chap: pending -> in_progress.
     * URL: PATCH /admin/disputes/{dispute}/process
     */
    public function process(Dispute $dispute): JsonResponse
    {
        if ($dispute->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể tiếp nhận tranh chấp đang ở trạng thái "Chờ xử lý".',
            ], 422);
        }

        $dispute->update(['status' => 'in_progress']);

        return response()->json([
            'message' => 'Đã tiếp nhận tranh chấp #'.$dispute->id.'. Đang trong quá trình xử lý.',
            'data' => $dispute->fresh(),
        ]);
    }

    /**
     * Hoan tien: refunded + tru balance seller + ghi log.
     * Bat buoc admin_decision (ly do hoan tien).
     * URL: PATCH /admin/disputes/{dispute}/refund
     */
    public function refund(Request $request, Dispute $dispute): JsonResponse
    {
        if (! in_array($dispute->status, ['pending', 'in_progress'])) {
            return response()->json([
                'message' => 'Không thể hoàn tiền tranh chấp đã được xử lý xong.',
            ], 422);
        }

        $validated = $request->validate([
            'admin_decision' => ['required', 'string', 'max:2000'],
        ], [
            'admin_decision.required' => 'Vui lòng nhập quyết định / lý do hoàn tiền.',
        ]);

        $sellerOrder = $dispute->sellerOrder;

        DB::transaction(function () use ($dispute, $sellerOrder, $validated) {
            // Cap nhat trang thai tranh chap
            $dispute->update([
                'status' => 'refunded',
                'admin_decision' => $validated['admin_decision'],
            ]);

            // Cap nhat trang thai seller order
            $sellerOrder->update(['status' => 'cancelled']);

            // Tru balance seller (lockForUpdate de tranh race condition)
            $sellerProfile = SellerProfile::where('user_id', $sellerOrder->seller_id)
                ->lockForUpdate()
                ->first();

            if ($sellerProfile) {
                $sellerProfile->update(['balance' => $sellerProfile->balance - $sellerOrder->grand_total]);

                // Ghi nhat ky balance log
                SellerBalanceLog::create([
                    'seller_id' => $sellerOrder->seller_id,
                    'amount' => -$sellerOrder->grand_total,
                    'type' => 'refund',
                    'reference_id' => $dispute->id,
                    'description' => 'Hoàn tiền cho tranh chấp #'.$dispute->id,
                ]);
            }
        });

        return response()->json([
            'message' => 'Đã xử lý hoàn tiền cho tranh chấp #'.$dispute->id.' thành công!',
            'data' => $dispute->fresh(),
        ]);
    }

    /**
     * Tu choi khieu nai: rejected + ghi ly do.
     * Balance KHONG thay doi.
     * URL: PATCH /admin/disputes/{dispute}/reject
     */
    public function reject(Request $request, Dispute $dispute): JsonResponse
    {
        if (! in_array($dispute->status, ['pending', 'in_progress'])) {
            return response()->json([
                'message' => 'Không thể từ chối tranh chấp đã được xử lý xong.',
            ], 422);
        }

        $validated = $request->validate([
            'admin_decision' => ['required', 'string', 'max:2000'],
        ], [
            'admin_decision.required' => 'Vui lòng nhập lý do từ chối khiếu nại.',
        ]);

        $dispute->update([
            'status' => 'rejected',
            'admin_decision' => $validated['admin_decision'],
        ]);

        return response()->json([
            'message' => 'Đã từ chối khiếu nại #'.$dispute->id.'.',
            'data' => $dispute->fresh(),
        ]);
    }

    /**
     * Xuat danh sach tranh chap ra CSV.
     * URL: GET /admin/disputes/export
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $disputes = Dispute::with([
            'buyer',
            'sellerOrder.order',
            'sellerOrder.seller.sellerProfile',
        ])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->whereHas('buyer', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->get();

        $filename = 'cupo-disputes-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($disputes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Người mua', 'Email', 'Mã đơn hàng', 'Gian hàng',
                'Lý do khiếu nại', 'Trạng thái', 'Phán quyết Admin', 'Ngày tạo',
            ]);

            $statusLabel = [
                'pending' => 'Chờ xử lý',
                'in_progress' => 'Đang xử lý',
                'refunded' => 'Đã hoàn tiền',
                'rejected' => 'Đã từ chối',
            ];

            foreach ($disputes as $d) {
                fputcsv($out, [
                    $d->id,
                    $d->buyer?->name ?? '',
                    $d->buyer?->email ?? '',
                    $d->sellerOrder?->order?->order_number ?? '',
                    $d->sellerOrder?->seller?->sellerProfile?->shop_name ?? '',
                    $d->reason,
                    $statusLabel[$d->status] ?? $d->status,
                    $d->admin_decision ?? '',
                    $d->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
