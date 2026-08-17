<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'registration_deadline' => ['nullable', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $deadline = $this->input('registration_deadline');
            $startsAt = $this->input('starts_at');

            if ($deadline && $startsAt) {
                $deadlineCarbon = Carbon::parse($deadline);
                $startsAtCarbon = Carbon::parse($startsAt);

                if ($deadlineCarbon->diffInMinutes($startsAtCarbon, false) < 10) {
                    $validator->errors()->add(
                        'registration_deadline',
                        'Han chot dang ky phai truoc thoi gian bat dau phien it nhat 10 phut.'
                    );
                }
            }
        });
    }
}
