<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'test_email' => ['required', 'email', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_email.required' => 'Vui lòng nhập địa chỉ email nhận thư thử nghiệm.',
            'test_email.email' => 'Địa chỉ email không đúng định dạng.',
        ];
    }
}
