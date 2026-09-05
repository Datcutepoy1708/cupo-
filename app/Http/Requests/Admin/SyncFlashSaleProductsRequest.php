<?php

namespace App\Http\Requests\Admin;

use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class SyncFlashSaleProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => ['present', 'array'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.flash_sale_price' => ['required', 'numeric', 'gt:0'],
            'products.*.quantity_limit' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $products = $this->input('products', []);
            $flashSale = $this->route('flashSale');

            foreach ($products as $index => $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);
                if (! $product) {
                    continue;
                }

                // Rule 1: Max 90% of regular price (flash_sale_price <= 0.9 * price, tinh theo bien the re nhat neu co bien the)
                $basePrice = $product->has_variants && $product->variants()->exists()
                    ? (float) $product->variants()->min('price')
                    : (float) $product->price;

                $maxPrice = bcmul((string) $basePrice, '0.90', 2);
                if (isset($item['flash_sale_price']) && (float) $item['flash_sale_price'] > (float) $maxPrice) {
                    $validator->errors()->add(
                        "products.{$index}.flash_sale_price",
                        'Giá Flash Sale phải nhỏ hơn hoặc bằng 90% giá gốc ('.number_format((float) $maxPrice, 0, ',', '.').' VNĐ).'
                    );
                }

                // Rule 2: quantity_limit <= product.stock (tong ton kho neu co bien the)
                $totalStock = $product->has_variants && $product->variants()->exists()
                    ? (int) $product->variants()->sum('stock')
                    : (int) $product->stock;

                if (isset($item['quantity_limit']) && (int) $item['quantity_limit'] > $totalStock) {
                    $validator->errors()->add(
                        "products.{$index}.quantity_limit",
                        "Số lượng Flash Sale không được vượt quá tồn kho thực tế ({$totalStock})."
                    );
                }

                // Rule 3: Check overlapping active flash sale sessions for same product
                if ($flashSale && $flashSale->starts_at && $flashSale->ends_at) {
                    $overlapping = FlashSaleProduct::where('product_id', $product->id)
                        ->where('flash_sale_id', '!=', $flashSale->id)
                        ->whereHas('flashSale', function ($q) use ($flashSale) {
                            $q->where('status', true)
                                ->where('starts_at', '<', $flashSale->ends_at)
                                ->where('ends_at', '>', $flashSale->starts_at);
                        })
                        ->exists();

                    if ($overlapping) {
                        $validator->errors()->add(
                            "products.{$index}.product_id",
                            "Sản phẩm '{$product->name}' đã tham gia vào một phiên Flash Sale khác cùng khoảng thời gian."
                        );
                    }
                }
            }
        });
    }
}
