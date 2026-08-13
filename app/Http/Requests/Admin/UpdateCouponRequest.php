<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id ?? $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'seller_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'in:fixed_amount,percentage'],
            'value' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'percentage' && $value > 100) {
                        $fail('Gia tri phan tram giam gia khong the vuot qua 100%.');
                    }
                },
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui long nhap ma giam gia.',
            'code.unique' => 'Ma giam gia nay da ton tai tren he thong.',
            'code.alpha_dash' => 'Ma giam gia chi duoc chua chu cai, so va dau gach.',
            'type.required' => 'Vui long chon loai giam gia.',
            'type.in' => 'Loai giam gia khong hop le.',
            'value.required' => 'Vui long nhap gia tri giam.',
            'value.numeric' => 'Gia tri giam phai la so.',
            'value.min' => 'Gia tri giam phai lon hon 0.',
            'usage_limit.required' => 'Vui long nhap gioi han luot su dung.',
            'usage_limit.min' => 'Gioi han luot su dung phai it nhat la 1.',
            'expires_at.after_or_equal' => 'Ngay het han phai dien ra sau hoac bang ngay bat dau.',
            'seller_id.exists' => 'Gian hang duoc chon khong hop le.',
        ];
    }
}
