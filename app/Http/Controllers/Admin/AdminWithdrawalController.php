<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerBalanceLog;
use App\Models\SellerProfile;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWithdrawalController extends Controller
{
    /**
     * Danh sach yeu cau rut tien cua Seller.
     * - Browser: Tra ve Blade view
     * - AJAX: Tra ve JSON paginate kem meta stats
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.withdrawals.index');
        }

        $status = $request->query('status');
        $keyword = $request->query('search');

        $withdrawals = Withdrawal::with(['seller.sellerProfile'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('bank_account', 'like', '%'.$keyword.'%')
                    ->orWhere('bank_owner', 'like', '%'.$keyword.'%')
                    ->orWhere('bank_name', 'like', '%'.$keyword.'%')
                    ->orWhereHas('seller', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%')
                        ->orWhereHas('sellerProfile', fn ($sq) => $sq->where('shop_name', 'like', '%'.$keyword.'%')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Withdrawal::selectRaw('status, count(*) as total, sum(case when status = "approved" then amount else 0 end) as total_paid')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalApprovedAmount = Withdrawal::where('status', 'approved')->sum('amount');

        return response()->json(array_merge($withdrawals->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_pending' => $counts->get('pending', 0),
                'total_approved' => $counts->get('approved', 0),
                'total_rejected' => $counts->get('rejected', 0),
                'total_paid' => (float) $totalApprovedAmount,
            ],
        ]));
    }

    /**
     * Chi tiet 1 yeu cau rut tien.
     * URL: GET /admin/withdrawals/{withdrawal}
     */
    public function show(Withdrawal $withdrawal): View
    {
        $withdrawal->load(['seller.sellerProfile']);

        $recentLogs = SellerBalanceLog::where('seller_id', $withdrawal->seller_id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.withdrawals.show', compact('withdrawal', 'recentLogs'));
    }

    /**
     * Duyet yeu cau rut tien: pending -> approved.
     * Kiem tra balance >= amount, tru tien seller_profiles.balance va ghi seller_balance_logs trong DB transaction.
     * URL: PATCH /admin/withdrawals/{withdrawal}/approve
     */
    public function approve(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể duyệt yêu cầu rút tiền đang ở trạng thái "Chờ duyệt".',
            ], 422);
        }

        $sellerProfile = SellerProfile::where('user_id', $withdrawal->seller_id)->first();

        if (! $sellerProfile || $sellerProfile->balance < $withdrawal->amount) {
            return response()->json([
                'message' => 'Số dư ví của Seller không đủ để thực hiện lệnh rút này (Số dư hiện tại: '.number_format($sellerProfile?->balance ?? 0).'đ).',
            ], 422);
        }

        DB::transaction(function () use ($withdrawal) {
            $lockedProfile = SellerProfile::where('user_id', $withdrawal->seller_id)
                ->lockForUpdate()
                ->first();

            if (! $lockedProfile || $lockedProfile->balance < $withdrawal->amount) {
                throw new \Exception('Số dư ví không đủ tại thời điểm khóa hàng.');
            }

            // Cap nhat trang thai withdrawal
            $withdrawal->update(['status' => 'approved']);

            // Tru tien vi seller
            $lockedProfile->decrement('balance', $withdrawal->amount);

            // Ghi nhat ky bien dong so du
            SellerBalanceLog::create([
                'seller_id' => $withdrawal->seller_id,
                'amount' => -$withdrawal->amount,
                'type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'description' => 'Rút tiền về tài khoản '.$withdrawal->bank_name.' ('.$withdrawal->bank_account.')',
            ]);
        });

        return response()->json([
            'message' => 'Đã duyệt yêu cầu rút tiền #'.$withdrawal->id.' thành công! Đã trừ '.number_format($withdrawal->amount).'đ từ ví của Seller.',
            'data' => $withdrawal->fresh(['seller.sellerProfile']),
        ]);
    }

    /**
     * Tu choi yeu cau rut tien: pending -> rejected.
     * Bat buoc co admin_note. So du vi cua Seller KHONG bi tru.
     * URL: PATCH /admin/withdrawals/{withdrawal}/reject
     */
    public function reject(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể từ chối yêu cầu rút tiền đang ở trạng thái "Chờ duyệt".',
            ], 422);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối yêu cầu rút tiền.',
        ]);

        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
        ]);

        return response()->json([
            'message' => 'Đã từ chối yêu cầu rút tiền #'.$withdrawal->id.'.',
            'data' => $withdrawal->fresh(['seller.sellerProfile']),
        ]);
    }

    /**
     * Xuat danh sach yeu cau rut tien ra file CSV (UTF-8 BOM).
     * URL: GET /admin/withdrawals/export
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $withdrawals = Withdrawal::with(['seller.sellerProfile'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('bank_account', 'like', '%'.$keyword.'%')
                    ->orWhere('bank_owner', 'like', '%'.$keyword.'%')
                    ->orWhere('bank_name', 'like', '%'.$keyword.'%')
                    ->orWhereHas('seller', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->get();

        $filename = 'cupo-withdrawals-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($withdrawals) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Tên gian hàng', 'Chủ gian hàng', 'Email', 'Ngân hàng',
                'Số tài khoản', 'Chủ tài khoản', 'Số tiền rút (VND)', 'Trạng thái',
                'Ghi chú Admin', 'Ngày yêu cầu',
            ]);

            foreach ($withdrawals as $w) {
                fputcsv($out, [
                    $w->id,
                    $w->seller?->sellerProfile?->shop_name ?? '',
                    $w->seller?->name ?? '',
                    $w->seller?->email ?? '',
                    $w->bank_name,
                    $w->bank_account,
                    $w->bank_owner,
                    $w->amount,
                    $w->status_label,
                    $w->admin_note ?? '',
                    $w->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
