<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class SellerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'business_type' => ['nullable', 'string', 'in:personal,company'],
            'description' => ['nullable', 'string'],
            'date_of_birth' => [
                'required',
                'date_format:d/m/Y',
                function ($attribute, $value, $fail) {
                    try {
                        $dob = Carbon::createFromFormat('d/m/Y', $value);
                        if ($dob->age < 18) {
                            $fail('Người bán phải từ đủ 18 tuổi trở lên.');
                        }
                    } catch (\Exception $e) {
                        $fail('Ngày sinh không đúng định dạng dd/mm/yyyy.');
                    }
                },
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'national_id' => [
                'required',
                'string',
                'regex:/^[0-9]{12}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.date_format' => 'Ngày sinh phải có định dạng dd/mm/yyyy (ví dụ: 15/08/2000).',
            'national_id.required' => 'Vui lòng nhập số căn cước công dân.',
            'national_id.regex' => 'Số căn cước công dân phải gồm đúng 12 chữ số.',
            'category_ids.*.exists' => 'Danh mục sản phẩm được chọn không hợp lệ.',
        ];
    }
}
