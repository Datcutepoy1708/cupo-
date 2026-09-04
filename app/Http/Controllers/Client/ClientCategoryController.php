<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ClientCategoryController extends Controller
{
    /**
     * Trang hiển thị danh sách tất cả các danh mục.
     */
    public function index(): View
    {
        $categories = Category::with(['children' => function ($q) {
            $q->where('status', true)->withCount('products');
        }])
            ->withCount(['products', 'children'])
            ->whereNull('parent_id')
            ->where('status', true)
            ->get();

        // 1. Banner đầu trang danh mục (category_top) qua Cache
        $categoryBanners = collect(Cache::remember('banners:category_top', 3600, function () {
            return Banner::active()->atPosition('category_top')->get()->toArray();
        }))->map(fn ($item) => (object) $item);
        $categoryBanner = $categoryBanners->first();

        // 2. Banner thanh bên (sidebar) qua Cache
        $sidebarBanners = collect(Cache::remember('banners:sidebar', 3600, function () {
            return Banner::active()->atPosition('sidebar')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        return view('client.categories.categories-list', compact(
            'categories',
            'categoryBanners',
            'categoryBanner',
            'sidebarBanners'
        ));
    }

    /**
     * Trang xem sản phẩm theo Danh mục (Shopee style listing).
     * Hỗ trợ lọc theo danh mục cha, danh mục con, khoảng giá, sắp xếp & địa điểm.
     */
    public function show(Request $request, string $slug): View
    {
        // 1. Tìm danh mục hiện tại theo slug
        $category = Category::with(['parent.children', 'children'])
            ->where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        // 2. Xác định danh mục gốc & mảng ID danh mục để truy vấn sản phẩm
        if ($category->parent_id === null) {
            // Đây là Danh mục Gốc (Cha)
            $rootCategory = $category;
            $selectedChild = null;
            $categoryIds = $category->children->pluck('id')->push($category->id)->toArray();
            $subCategories = $category->children;
        } else {
            // Đây là Danh mục Con
            $rootCategory = $category->parent;
            $selectedChild = $category;
            $categoryIds = [$category->id];
            $subCategories = $rootCategory ? $rootCategory->children : collect([$category]);
        }

        // 3. Lấy tất cả danh mục gốc (kèm danh mục con) cho Sidebar "Tất Cả Danh Mục" qua Redis Cache
        $allRootCategories = collect(Cache::remember('categories:all_tree', 86400, function () {
            return Category::with(['children' => function ($q) {
                $q->where('status', true);
            }])
                ->whereNull('parent_id')
                ->where('status', true)
                ->get()
                ->toArray();
        }))->map(function ($cat) {
            $catObj = (object) $cat;
            $catObj->children = collect($cat['children'] ?? [])->map(fn ($c) => (object) $c);

            return $catObj;
        });

        // 4. Banner đầu trang danh mục (category_top) qua Redis Cache
        $categoryBanners = collect(Cache::remember('banners:category_top', 3600, function () {
            return Banner::active()->atPosition('category_top')->get()->toArray();
        }))->map(fn ($item) => (object) $item);
        $categoryBanner = $categoryBanners->first();

        // Banner thanh bên (sidebar) qua Redis Cache
        $sidebarBanners = collect(Cache::remember('banners:sidebar', 3600, function () {
            return Banner::active()->atPosition('sidebar')->get()->toArray();
        }))->map(fn ($item) => (object) $item);

        // 5. Khởi tạo truy vấn sản phẩm
        $query = Product::with(['seller.sellerProfile', 'category'])
            ->where('status', 'approved')
            ->whereIn('category_id', $categoryIds);

        // --- Lọc theo Khoảng giá ---
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        // --- Lọc theo Nơi bán / Địa chỉ ---
        if ($request->filled('location')) {
            $loc = $request->input('location');
            $query->whereHas('seller.sellerProfile', function ($q) use ($loc) {
                $q->where('address', 'LIKE', '%'.$loc.'%');
            });
        }

        // --- Sắp xếp sản phẩm (Sort) ---
        $sort = $request->input('sort', 'popular');
        switch ($sort) {
            case 'latest':
                $query->latest();
                break;
            case 'bestseller':
                $query->orderBy('stock', 'asc'); // Ưu tiên hàng bán chạy
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
            default:
                $query->latest();
                break;
        }

        // 6. Phân trang sản phẩm (16 sản phẩm / trang)
        $products = $query->paginate(16)->withQueryString();

        return view('client.categories.show', compact(
            'category',
            'rootCategory',
            'selectedChild',
            'subCategories',
            'allRootCategories',
            'categoryBanners',
            'categoryBanner',
            'sidebarBanners',
            'products',
            'sort'
        ));
    }
}
