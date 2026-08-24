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
                function ($attribute, $value, $fail) {
                    $dob = null;
                    try {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $dob = Carbon::createFromFormat('Y-m-d', $value);
                        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                            $dob = Carbon::createFromFormat('d/m/Y', $value);
                        } else {
                            $dob = Carbon::parse($value);
                        }
                    } catch (\Exception $e) {
                        return $fail('Ngày sinh không đúng định dạng hợp lệ.');
                    }

                    if (! $dob || $dob->isFuture()) {
                        return $fail('Ngày sinh không hợp lệ.');
                    }

                    if ($dob->age < 18) {
                        return $fail('Người bán phải từ đủ 18 tuổi trở lên.');
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
