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

    protected function prepareForValidation(): void
    {
        if ($this->has('has_variants')) {
            $this->merge([
                'has_variants' => filter_var($this->has_variants, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if (is_string($this->input('attributes'))) {
            $decoded = json_decode($this->input('attributes'), true);
            if (is_array($decoded)) {
                $this->merge(['attributes' => $decoded]);
            }
        }

        if (is_string($this->input('variants'))) {
            $decoded = json_decode($this->input('variants'), true);
            if (is_array($decoded)) {
                $this->merge(['variants' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        $hasVariants = $this->boolean('has_variants');

        $rules = [
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
            'has_variants' => ['nullable', 'boolean'],
            'attributes' => ['nullable', 'array'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.image_path' => ['nullable', 'string'],
            'thumbnail' => ['nullable'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
        ];

        if ($hasVariants) {
            $rules['price'] = ['nullable', 'numeric', 'min:0'];
            $rules['sale_price'] = ['nullable', 'numeric', 'min:0'];
            $rules['stock'] = ['nullable', 'integer', 'min:0'];
            $rules['variants'] = ['required', 'array', 'min:1'];
        } else {
            $rules['price'] = ['required', 'numeric', 'min:0'];
            $rules['sale_price'] = ['nullable', 'numeric', 'min:0', 'lt:price'];
            $rules['stock'] = ['required', 'integer', 'min:0'];
        }

        return $rules;
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
            'variants.required' => 'Vui lòng thiết lập ít nhất 1 biến thể khi bật phân loại hàng.',
            'variants.min' => 'Vui lòng thiết lập ít nhất 1 biến thể khi bật phân loại hàng.',
            'thumbnail.required' => 'Vui lòng cung cấp ảnh đại diện sản phẩm.',
            'description.required' => 'Vui lòng nhập mô tả chi tiết sản phẩm.',
        ];
    }
}
