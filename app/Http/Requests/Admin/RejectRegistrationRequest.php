<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Bat buoc co ly do khi tu choi — Rule 16 trong AGENT.md
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Vui long nhap ly do tu choi.',
            'rejection_reason.min' => 'Ly do tu choi phai co it nhat 5 ky tu.',
        ];
    }
}
