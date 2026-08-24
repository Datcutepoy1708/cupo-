<?php

namespace App\Http\Requests\Seller;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $sellerProfile = auth()->user()?->sellerProfile;
                    if ($sellerProfile) {
                        $registeredIds = $sellerProfile->categories()->pluck('categories.id')->toArray();
                        if (! empty($registeredIds)) {
                            $category = Category::find($value);
                            $isAllowed = in_array($value, $registeredIds) || ($category && in_array($category->parent_id, $registeredIds));
                            if (! $isAllowed) {
                                $fail('Loại sản phẩm này không thuộc các ngành hàng bạn đã đăng ký với sàn.');
                            }
                        }
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'thumbnail' => ['nullable'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'category_id.exists' => 'Danh mục sản phẩm không hợp lệ.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'sku.required' => 'Vui lòng nhập mã SKU sản phẩm.',
            'sku.unique' => 'Mã SKU này đã tồn tại trên hệ thống.',
            'price.required' => 'Vui lòng nhập giá bán sản phẩm.',
            'price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'sale_price.numeric' => 'Giá khuyến mãi phải là một số.',
            'sale_price.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá bán gốc.',
            'stock.required' => 'Vui lòng nhập số lượng tồn kho.',
            'thumbnail.required' => 'Vui lòng cung cấp ảnh đại diện sản phẩm.',
            'description.required' => 'Vui lòng nhập mô tả chi tiết sản phẩm.',
        ];
    }
}
