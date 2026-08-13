<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá (từ 1 đến 5 sao).',
            'rating.min' => 'Số sao đánh giá thấp nhất là 1 sao.',
            'rating.max' => 'Số sao đánh giá cao nhất là 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá sản phẩm.',
            'comment.min' => 'Nội dung đánh giá phải có ít nhất 5 ký tự.',
        ];
    }
}
