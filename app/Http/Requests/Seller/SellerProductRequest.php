<?php

namespace App\Http\Requests\Seller;

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
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'thumbnail' => ['required', 'string'],
            'description' => ['required', 'string'],
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
            'stock.required' => 'Vui lòng nhập số lượng tồn kho.',
            'thumbnail.required' => 'Vui lòng cung cấp ảnh đại diện sản phẩm.',
            'description.required' => 'Vui lòng nhập mô tả chi tiết sản phẩm.',
        ];
    }
}
