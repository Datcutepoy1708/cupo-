<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminActivityLogController extends Controller
{
    /**
     * Danh sách nhật ký hoạt động của nhân viên.
     */
    public function index(Request $request): View|JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin'])) {
            abort(403, 'Bạn không có quyền truy cập Nhật ký hoạt động!');
        }

        if (! $request->wantsJson()) {
            $staffUsers = User::whereIn('role', ['super-admin', 'admin', 'moderator', 'accountant'])
                ->select('id', 'name', 'email', 'role')
                ->orderBy('name')
                ->get();

            $modules = [
                'withdrawals' => 'Tài chính & Rút tiền',
                'sellers' => 'Gian hàng & Seller',
                'products' => 'Sản phẩm',
                'disputes' => 'Tranh chấp & Khiếu nại',
                'shipping' => 'Vận chuyển & Đối tác',
                'coupons' => 'Mã giảm giá',
                'settings' => 'Cấu hình hệ thống',
                'roles' => 'Phân quyền & Chức vụ',
                'auth' => 'Xác thực & Bảo mật',
            ];

            return view('admin.activity-logs.index', compact('staffUsers', 'modules'));
        }

        $userId = $request->query('user_id');
        $module = $request->query('module');
        $action = $request->query('action');
        $keyword = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = AdminActivityLog::with('user:id,name,email,role')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', Carbon::parse($dateFrom)))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', Carbon::parse($dateTo)))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('description', 'like', "%{$keyword}%")
                        ->orWhere('ip_address', 'like', "%{$keyword}%")
                        ->orWhere('action', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            });

        $logs = $query->latest('id')->paginate(15)->withQueryString();

        $today = Carbon::today();
        $stats = [
            'total_logs' => AdminActivityLog::count(),
            'today_logs' => AdminActivityLog::whereDate('created_at', $today)->count(),
            'sensitive_logs' => AdminActivityLog::whereIn('action', [
                'approve_withdrawal', 'reject_withdrawal', 'block_seller',
                'reject_seller', 'refund_dispute', 'update_settings', 'roles.manage',
            ])->count(),
            'auth_logs' => AdminActivityLog::where('module', 'auth')->count(),
        ];

        return response()->json(array_merge($logs->toArray(), [
            'meta' => $stats,
        ]));
    }

    /**
     * Xem chi tiết một bản ghi nhật ký.
     */
    public function show(AdminActivityLog $activityLog): JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin'])) {
            abort(403, 'Bạn không có quyền truy cập Nhật ký hoạt động!');
        }

        $activityLog->load('user:id,name,email,role');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $activityLog->id,
                'user' => $activityLog->user,
                'action' => $activityLog->action,
                'module' => $activityLog->module,
                'module_label' => $activityLog->module_label,
                'description' => $activityLog->description,
                'properties' => $activityLog->properties,
                'ip_address' => $activityLog->ip_address,
                'user_agent' => $activityLog->user_agent,
                'created_at' => $activityLog->created_at->format('H:i:s d/m/Y'),
            ],
        ]);
    }

    /**
     * Xuất danh sách nhật ký ra file CSV (UTF-8 BOM).
     */
    public function export(Request $request): StreamedResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin'])) {
            abort(403, 'Bạn không có quyền xuất dữ liệu kiểm toán!');
        }

        $userId = $request->query('user_id');
        $module = $request->query('module');
        $keyword = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $logs = AdminActivityLog::with('user:id,name,email,role')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', Carbon::parse($dateFrom)))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', Carbon::parse($dateTo)))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('description', 'like', "%{$keyword}%")
                        ->orWhere('ip_address', 'like', "%{$keyword}%")
                        ->orWhere('action', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            })
            ->latest('id')
            ->get();

        $filename = 'cupo-audit-logs-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($logs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, [
                'ID', 'Thời gian', 'Nhân viên thực hiện', 'Email', 'Vai trò',
                'Phân hệ', 'Hành động', 'Mô tả chi tiết', 'Địa chỉ IP',
            ]);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    $log->user->email ?? 'N/A',
                    $log->user->role ?? 'N/A',
                    $log->module_label,
                    $log->action,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
