<?php

namespace App\Http\Requests\Seller;

use App\Models\FlashSale;
use App\Models\FlashSaleRegistration;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flash_sale_id' => ['required', 'integer', 'exists:flash_sales,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'proposed_price' => ['required', 'numeric', 'gt:0'],
            'proposed_quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $seller = $this->user();
            $flashSaleId = $this->input('flash_sale_id');
            $productId = $this->input('product_id');

            // 1. Flash Sale phai dang mo dang ky
            $flashSale = FlashSale::find($flashSaleId);
            if ($flashSale && ! $flashSale->isRegistrationOpen()) {
                $validator->errors()->add(
                    'flash_sale_id',
                    'Phien Flash Sale nay khong con nhan dang ky (da qua han chot hoac chua mo).'
                );
            }

            // 2. San pham phai thuoc ve Seller dang dang nhap
            $product = Product::find($productId);
            if ($product && (int) $product->seller_id !== (int) $seller->id) {
                $validator->errors()->add(
                    'product_id',
                    'San pham nay khong thuoc ve gian hang cua ban.'
                );
            }

            // 3. Gia de xuat phai <= 90% gia goc (neu co bien the thi tinh theo bien the re nhat)
            if ($product && $this->input('proposed_price')) {
                $basePrice = $product->has_variants && $product->variants()->exists()
                    ? (float) $product->variants()->min('price')
                    : (float) $product->price;

                $maxPrice = bcmul((string) $basePrice, '0.90', 2);
                if ((float) $this->input('proposed_price') > (float) $maxPrice) {
                    $hint = $product->has_variants && $product->variants()->exists()
                        ? ' (tính theo biến thể rẻ nhất: '.number_format((float) $basePrice, 0, ',', '.').' VND)'
                        : '';
                    $validator->errors()->add(
                        'proposed_price',
                        'Giá đề xuất phải nhỏ hơn hoặc bằng 90% giá gốc'. $hint .' (tối đa '.number_format((float) $maxPrice, 0, ',', '.').' VND).'
                    );
                }
            }

            // 4. So luong de xuat khong duoc vuot ton kho (tong ton kho cua tat ca bien the)
            if ($product && $this->input('proposed_quantity')) {
                $totalStock = $product->has_variants && $product->variants()->exists()
                    ? (int) $product->variants()->sum('stock')
                    : (int) $product->stock;

                if ((int) $this->input('proposed_quantity') > $totalStock) {
                    $validator->errors()->add(
                        'proposed_quantity',
                        'Số lượng đề xuất không được vượt tồn kho thực tế ('.$totalStock.').'
                    );
                }
            }

            // 5. Khong duoc dang ky trung san pham trong cung 1 phien
            if ($flashSaleId && $productId) {
                $duplicate = FlashSaleRegistration::where('flash_sale_id', $flashSaleId)
                    ->where('product_id', $productId)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'product_id',
                        'San pham nay da co dang ky trong phien Flash Sale nay roi. Moi phien chi duoc dang ky 1 lan cho 1 san pham.'
                    );
                }
            }
        });
    }
}
