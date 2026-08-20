<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends Controller
{
    /**
     * Màn hình Báo cáo Doanh thu & Thống kê Tài chính Sàn.
     */
    public function index(Request $request, AnalyticsService $analytics): View|JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin', 'accountant'])) {
            abort(403, 'Bạn không có quyền truy cập Báo cáo Tài chính & Doanh thu!');
        }

        $period = $request->query('period', '7_days');
        $dateFrom = $request->query('date_from') ? Carbon::parse($request->query('date_from')) : null;
        $dateTo = $request->query('date_to') ? Carbon::parse($request->query('date_to')) : null;
        $keyword = $request->query('search');

        $kpis = $analytics->getOverviewKpis($dateFrom, $dateTo);
        $trend = $analytics->getRevenueTrend($period, $dateFrom, $dateTo);
        $categoryShare = $analytics->getCategoryShare();
        $topSellers = $analytics->getTopSellers(5);
        $topProducts = $analytics->getTopProducts(5);
        $reconciliations = $analytics->getSellerReconciliation($keyword, 10);

        if ($request->wantsJson()) {
            return response()->json([
                'kpis' => $kpis,
                'trend' => $trend,
                'category_share' => $categoryShare,
                'top_sellers' => $topSellers,
                'top_products' => $topProducts,
                'reconciliations' => $reconciliations,
            ]);
        }

        return view('admin.analytics.index', compact(
            'kpis',
            'trend',
            'categoryShare',
            'topSellers',
            'topProducts',
            'reconciliations',
            'period'
        ));
    }

    /**
     * Xuất file CSV Báo cáo Đối soát Tài chính Sàn & Seller (UTF-8 BOM).
     */
    public function export(Request $request, AnalyticsService $analytics): StreamedResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin', 'accountant'])) {
            abort(403, 'Bạn không có quyền xuất Báo cáo Tài chính!');
        }

        $keyword = $request->query('search');
        $data = $analytics->getSellerReconciliation($keyword, 500);

        $filename = 'cupo-financial-report-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, [
                'Mã Seller', 'Tên Gian Hàng', 'Chủ Gian Hàng', 'Email',
                'Tổng GMV Bán Được (VND)', 'Phí Hoa Hồng Sàn (VND)',
                'Doanh Thu Thực Của Shop (VND)', 'Đã Rút Về Tài Khoản (VND)', 'Số Dư Khả Dụng (VND)',
            ]);

            foreach ($data as $item) {
                fputcsv($out, [
                    $item['seller_id'],
                    $item['shop_name'],
                    $item['owner_name'],
                    $item['email'],
                    number_format($item['total_gmv']),
                    number_format($item['total_commission']),
                    number_format($item['net_earnings']),
                    number_format($item['total_withdrawn']),
                    number_format($item['available_balance']),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
