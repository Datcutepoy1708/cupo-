<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerSupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSupportTicketController extends Controller
{
    /**
     * Danh sach cac yeu cau ho tro / khang nghi cua Seller.
     * - Browser: Tra ve Blade view
     * - AJAX: Tra ve JSON paginate kem meta stat
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.support-tickets.index');
        }

        $status = $request->query('status');
        $category = $request->query('category');
        $keyword = $request->query('search');

        $tickets = SellerSupportTicket::with(['seller.sellerProfile', 'resolvedBy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('subject', 'like', '%'.$keyword.'%')
                    ->orWhereHas('seller', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%')
                        ->orWhereHas('sellerProfile', fn ($sq) => $sq->where('shop_name', 'like', '%'.$keyword.'%')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = SellerSupportTicket::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($tickets->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_open' => $counts->get('open', 0),
                'total_in_review' => $counts->get('in_review', 0),
                'total_resolved' => $counts->get('resolved', 0),
                'total_closed' => $counts->get('closed', 0),
            ],
        ]));
    }

    /**
     * Chi tiet 1 Ticket ho tro / khang nghi.
     * URL: GET /admin/support-tickets/{ticket}
     */
    public function show(SellerSupportTicket $ticket): View
    {
        $ticket->load(['seller.sellerProfile', 'resolvedBy']);

        return view('admin.support-tickets.show', compact('ticket'));
    }

    /**
     * Tiep nhan xu ly: open -> in_review.
     * URL: PATCH /admin/support-tickets/{ticket}/in-review
     */
    public function inReview(SellerSupportTicket $ticket): JsonResponse
    {
        if ($ticket->status !== 'open') {
            return response()->json([
                'message' => 'Chỉ có thể tiếp nhận yêu cầu đang ở trạng thái "Mới mở".',
            ], 422);
        }

        $ticket->update(['status' => 'in_review']);

        return response()->json([
            'message' => 'Đã tiếp nhận yêu cầu #'.$ticket->id.'. Đang xử lý.',
            'data' => $ticket->fresh(['seller.sellerProfile', 'resolvedBy']),
        ]);
    }

    /**
     * Phan hoi va giai quyet/dong ticket.
     * URL: PATCH /admin/support-tickets/{ticket}/respond
     */
    public function respond(Request $request, SellerSupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'max:3000'],
            'action_status' => ['required', 'in:resolved,closed'],
        ], [
            'admin_response.required' => 'Vui lòng nhập nội dung câu trả lời cho Seller.',
            'action_status.required' => 'Vui lòng chọn trạng thái hoàn tất.',
        ]);

        $ticket->update([
            'admin_response' => $validated['admin_response'],
            'status' => $validated['action_status'],
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $statusMsg = $validated['action_status'] === 'resolved' ? 'Đã giải quyết' : 'Đã đóng';

        return response()->json([
            'message' => "Đã gửi phản hồi thành công và đánh dấu: {$statusMsg}!",
            'data' => $ticket->fresh(['seller.sellerProfile', 'resolvedBy']),
        ]);
    }

    /**
     * Xuat danh sach ticket sang file CSV (UTF-8 BOM).
     * URL: GET /admin/support-tickets/export
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $category = $request->query('category');
        $keyword = $request->query('search');

        $tickets = SellerSupportTicket::with(['seller.sellerProfile', 'resolvedBy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('subject', 'like', '%'.$keyword.'%')
                    ->orWhereHas('seller', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->get();

        $filename = 'cupo-support-tickets-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($tickets) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Gian hàng', 'Chủ shop', 'Email', 'Danh mục',
                'Tiêu đề', 'Nội dung', 'Trạng thái', 'Phản hồi Admin', 'Người xử lý', 'Ngày tạo',
            ]);

            foreach ($tickets as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->seller?->sellerProfile?->shop_name ?? '',
                    $t->seller?->name ?? '',
                    $t->seller?->email ?? '',
                    $t->category_label,
                    $t->subject,
                    $t->message,
                    $t->status_label,
                    $t->admin_response ?? '',
                    $t->resolvedBy?->name ?? '',
                    $t->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
