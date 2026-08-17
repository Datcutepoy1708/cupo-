<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * GET /admin/dashboard
     * Tra ve Blade view voi du lieu thong ke thuc tu DB.
     */
    public function index(): View
    {
        // ---- Stat Cards ----
        $totalOrders = Order::count();
        $revenueThisMonth = Order::where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $revenueLastMonth = Order::where('payment_status', 'paid')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('grand_total');

        $totalCustomers = User::where('role', 'customer')->count();
        $activeShops = SellerProfile::where('status', 'approved')->count();

        // Tinh % tang truong so voi thang truoc (tranh chia cho 0)
        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        // ---- Bang "Gian hang cho duyet" (5 moi nhat) ----
        $pendingSellers = SellerProfile::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // ---- Bang "San pham cho duyet" (5 moi nhat) ----
        $pendingProducts = Product::with('seller.sellerProfile')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // ---- Bang "Don hang gan day" (8 moi nhat) ----
        $recentOrders = Order::with(['user', 'sellerOrders.seller.sellerProfile'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'revenueThisMonth',
            'revenueLastMonth',
            'revenueGrowth',
            'totalCustomers',
            'activeShops',
            'pendingSellers',
            'pendingProducts',
            'recentOrders',
        ));
    }
}
