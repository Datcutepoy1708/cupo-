<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerUpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['confirmed', 'shipping', 'completed', 'cancelled'])],
            'tracking_number' => ['required_if:status,shipping', 'nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái mới cho đơn hàng.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'tracking_number.required_if' => 'Vui lòng nhập mã vận đơn khi chuyển đơn hàng sang trạng thái đang giao.',
        ];
    }
}
