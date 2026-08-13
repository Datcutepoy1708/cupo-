<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyProductSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/dummy.json');
        if (! file_exists($jsonPath)) {
            $this->command->error('Tệp dummy.json không tồn tại!');

            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);
        $products = $data['products'] ?? [];

        if (empty($products)) {
            $this->command->error('Không có sản phẩm nào trong tệp dummy.json!');

            return;
        }

        // Lấy danh sách Sellers và Categories
        $sellers = User::where('role', 'seller')->get();
        if ($sellers->isEmpty()) {
            $this->command->error('Chưa có Seller nào trong cơ sở dữ liệu!');

            return;
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->error('Chưa có Category nào trong cơ sở dữ liệu!');

            return;
        }

        // Xóa các sản phẩm cũ trước khi nạp lại
        ProductImage::query()->delete();
        Product::query()->delete();

        // Ánh xạ chi tiết các ngành hàng từ dummy.json tới các DANH MỤC CON
        $categoryMapping = [
            'beauty' => 'Dụng cụ Chăm sóc cá nhân',
            'skin-care' => 'Thực phẩm chức năng & Vitamin',
            'fragrances' => 'Cân sức khỏe & Đo huyết áp',
            'furniture' => 'Trang trí nhà cửa & Cây cảnh',
            'home-decoration' => 'Đèn & Thiết bị chiếu sáng',
            'kitchen-accessories' => 'Dụng cụ Bếp & Nấu nướng',
            'groceries' => 'E-Voucher Mua sắm & Ăn uống',
            'laptops' => 'Gaming Laptop',
            'mobile-accessories' => 'Cáp sạc & Sạc dự phòng',
            'mens-shirts' => 'Áo sơ mi Nam',
            'mens-shoes' => 'Giày Nam & Sneaker',
            'mens-watches' => 'Đồng hồ Nam cao cấp',
            'womens-watches' => 'Đồng hồ Nữ thời trang',
            'motorcycle' => 'Phụ tùng & Dầu nhớt Xe máy',
            'sports-accessories' => 'Đồ tập Gym & Yoga',
            'sunglasses' => 'Kính cường lực',
            'tops' => 'Áo thun & Áo kiểu Nữ',
            'womens-bags' => 'Túi xách & Ví Nữ',
            'womens-dresses' => 'Váy & Đầm Nữ',
            'womens-jewellery' => 'Đồ ngủ Nữ',
            'womens-shoes' => 'Giày thể thao Nam & Nữ',
        ];

        // Lấy danh sách tất cả các DANH MỤC CON
        $childCategories = Category::whereNotNull('parent_id')->get();
        if ($childCategories->isEmpty()) {
            $childCategories = $categories;
        }

        $count = 0;
        foreach ($products as $index => $item) {
            // Pick seller (round-robin)
            $seller = $sellers[$index % $sellers->count()];

            // Phân bổ sản phẩm xoay vòng đều cho 100% tất cả danh mục con!
            $category = $childCategories[$index % $childCategories->count()];

            // Convert USD price to VND (e.g. 1 USD = 25,000 VND)
            $priceUsd = floatval($item['price'] ?? 10);
            $priceVnd = round(($priceUsd * 25000) / 1000) * 1000;
            if ($priceVnd < 10000) {
                $priceVnd = 50000;
            }

            // Generate slug
            $slug = Str::slug($item['title']);
            if (Product::where('slug', $slug)->exists()) {
                $slug .= '-'.Str::random(4);
            }

            // Generate SKU
            $sku = $item['sku'] ?? ('SKU-'.strtoupper(Str::random(8)));
            if (Product::where('sku', $sku)->exists()) {
                $sku .= '-'.rand(10, 99);
            }

            // Status: 85% approved, 15% pending
            $status = ($index % 6 === 0) ? 'pending' : 'approved';

            // Tạo mô tả chi tiết HTML gồm văn bản và các hình ảnh của sản phẩm
            $baseDesc = e($item['description'] ?? 'Sản phẩm chất lượng cao nhập khẩu chính hãng.');
            $imagesHtml = '';
            if (! empty($item['images']) && is_array($item['images'])) {
                $imagesHtml .= '<div class="product-desc-gallery mt-3 d-flex flex-wrap gap-2">';
                foreach (array_slice($item['images'], 0, 4) as $imgUrl) {
                    $imagesHtml .= '<div class="desc-img-item" style="width:130px; height:130px; border-radius:8px; overflow:hidden; border:1px solid #dee2e6; background:#f8f9fa; display:inline-block;">';
                    $imagesHtml .= '<img src="'.e($imgUrl).'" style="width:100%; height:100%; object-fit:cover;" alt="Hình ảnh sản phẩm">';
                    $imagesHtml .= '</div>';
                }
                $imagesHtml .= '</div>';
            }
            $fullDescription = '<p class="mb-2">'.$baseDesc.'</p>'.$imagesHtml;

            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'name' => $item['title'],
                'slug' => $slug,
                'sku' => $sku,
                'price' => $priceVnd,
                'has_variants' => false,
                'stock' => intval($item['stock'] ?? 50),
                'thumbnail' => $item['thumbnail'] ?? ($item['images'][0] ?? null),
                'description' => $fullDescription,
                'short_description' => Str::limit($item['description'] ?? '', 120),
                'status' => $status,
            ]);

            // Thêm các ảnh gallery nếu có
            if (! empty($item['images']) && is_array($item['images'])) {
                foreach ($item['images'] as $imgIndex => $imgUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imgUrl,
                        'sort_order' => $imgIndex + 1,
                    ]);
                }
            }

            $count++;
        }

        $this->command->info("Đã tạo thành công {$count} sản phẩm từ dummy.json cho các Seller!");
    }
}
