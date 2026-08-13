<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRejectResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware role:admin đã bảo vệ ở vòng ngoài
    }

    public function rules(): array
    {
        return [
            'admin_note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối/khóa.',
            'admin_note.string' => 'Lý do phải là chuỗi văn bản.',
            'admin_note.max' => 'Lý do không được vượt quá 1000 ký tự.',
        ];
    }
}
