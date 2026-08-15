<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateSellerOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:pending,confirmed,shipping,completed,cancelled',
            ],
            // Bat buoc khi chuyen sang "dang giao" (Rule 16 AGENT.md: ly do bat buoc)
            'tracking_number' => [
                'nullable',
                'string',
                'max:100',
                'required_if:status,shipping',
            ],
            // Bat buoc khi Admin huy don hang (Rule 16)
            'cancel_reason' => [
                'nullable',
                'string',
                'min:5',
                'max:500',
                'required_if:status,cancelled',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'tracking_number.required_if' => 'Mã vận đơn bắt buộc khi chuyển sang trạng thái Đang giao.',
            'tracking_number.max' => 'Mã vận đơn tối đa 100 ký tự.',
            'cancel_reason.required_if' => 'Lý do hủy đơn là bắt buộc.',
            'cancel_reason.min' => 'Lý do hủy phải có ít nhất 5 ký tự.',
        ];
    }
}
