<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCustomerController extends Controller
{
    /**
     * Danh sách khách hàng cho Admin.
     * - Browser request  -> trả Blade view (admin.customers.index)
     * - AJAX / JSON      -> trả JSON paginate kèm meta đếm từng trạng thái
     *
     * Query params:
     *   status  = active | blocked
     *   search  = từ khóa tìm kiếm (tên, email, SĐT)
     *   page    = số trang
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.customers.index');
        }

        $status = $request->query('status');
        $keyword = $request->query('search');

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Dem so luong theo tung trang thai
        $counts = User::where('role', 'customer')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $newIn30d = User::where('role', 'customer')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json(array_merge($customers->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_active' => $counts->get('active', 0),
                'total_blocked' => $counts->get('blocked', 0),
                'total_new_30d' => $newIn30d,
            ],
        ]));
    }

    /**
     * Hồ sơ chi tiết 1 khách hàng.
     * Chỉ cho phép xem tài khoản có role = customer.
     * URL: GET /admin/customers/{user}
     */
    public function show(User $user): View|JsonResponse
    {
        abort_if($user->role !== 'customer', 403, 'Tài khoản này không phải khách hàng.');

        $user->load([
            'addresses',
            'orders' => fn ($q) => $q->latest()->limit(10),
            'orders.sellerOrders',
        ]);

        if (request()->wantsJson()) {
            return response()->json(['data' => $user]);
        }

        return view('admin.customers.show', compact('user'));
    }

    /**
     * Khóa tài khoản khách hàng -> status = blocked
     * Bắt buộc truyền admin_note (lý do khóa).
     * URL: PATCH /admin/customers/{user}/block
     */
    public function block(Request $request, User $user): JsonResponse
    {
        abort_if($user->role !== 'customer', 403, 'Tài khoản này không phải khách hàng.');

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do khóa tài khoản.',
        ]);

        $user->update(['status' => 'blocked']);

        return response()->json([
            'message' => "Đã khóa tài khoản {$user->name}. Lý do: {$validated['admin_note']}",
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Mở khóa tài khoản khách hàng -> status = active
     * URL: PATCH /admin/customers/{user}/unblock
     */
    public function unblock(User $user): JsonResponse
    {
        abort_if($user->role !== 'customer', 403, 'Tài khoản này không phải khách hàng.');

        $user->update(['status' => 'active']);

        return response()->json([
            'message' => "Đã mở khóa tài khoản {$user->name} thành công!",
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Xuất danh sách khách hàng dưới dạng CSV (UTF-8 BOM cho Excel).
     * URL: GET /admin/customers/export
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%');
            })
            ->latest()
            ->get();

        $statusLabel = [
            'active' => 'Hoạt động',
            'blocked' => 'Đã khóa',
        ];

        $filename = 'cupo-customers-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($customers, $statusLabel) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM (Excel mo dung tieng Viet)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Họ tên', 'Email', 'Số điện thoại',
                'Ngày sinh', 'Tổng đơn hàng', 'Trạng thái', 'Ngày tạo tài khoản',
            ]);

            foreach ($customers as $c) {
                fputcsv($out, [
                    $c->id,
                    $c->name,
                    $c->email,
                    $c->phone ?? '',
                    $c->date_of_birth?->format('d/m/Y') ?? '',
                    $c->orders_count,
                    $statusLabel[$c->status] ?? $c->status,
                    $c->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
