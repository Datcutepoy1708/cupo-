<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            // General
            'site_name' => ['nullable', 'string', 'max:200'],
            'site_tagline' => ['nullable', 'string', 'max:300'],
            'site_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'site_favicon' => ['nullable', 'image', 'mimes:ico,png,svg', 'max:1024'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'maintenance_mode' => ['nullable', 'in:0,1'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],

            // Seller
            'default_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'auto_approve_sellers' => ['nullable', 'in:0,1'],
            'min_withdrawal_amount' => ['nullable', 'numeric', 'min:10000'],
            'max_withdrawal_amount' => ['nullable', 'numeric', 'gte:min_withdrawal_amount'],
            'escrow_holding_days' => ['nullable', 'integer', 'min:0', 'max:30'],

            // Order
            'default_shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'auto_cancel_pending_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'enable_cod' => ['nullable', 'in:0,1'],

            // Payment
            'enable_vnpay' => ['nullable', 'in:0,1'],
            'vnpay_tmn_code' => ['nullable', 'string', 'max:50'],
            'vnpay_hash_secret' => ['nullable', 'string', 'max:100'],
            'vnpay_sandbox' => ['nullable', 'in:0,1'],
            'enable_momo' => ['nullable', 'in:0,1'],
            'momo_partner_code' => ['nullable', 'string', 'max:50'],
            'momo_access_key' => ['nullable', 'string', 'max:100'],
            'momo_secret_key' => ['nullable', 'string', 'max:100'],
            'momo_sandbox' => ['nullable', 'in:0,1'],

            // Mail
            'mail_mailer' => ['nullable', 'string', 'max:20'],
            'mail_host' => ['nullable', 'string', 'max:100'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:100'],
            'mail_password' => ['nullable', 'string', 'max:100'],
            'mail_encryption' => ['nullable', 'string', 'max:20'],
            'mail_from_address' => ['nullable', 'email', 'max:100'],
            'mail_from_name' => ['nullable', 'string', 'max:100'],

            // SEO & Social
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:300'],
            'og_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_zalo' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'default_commission_rate.min' => 'Tỷ lệ hoa hồng không được nhỏ hơn 0%.',
            'default_commission_rate.max' => 'Tỷ lệ hoa hồng không được vượt quá 100%.',
            'min_withdrawal_amount.min' => 'Hạn mức rút tiền tối thiểu ít nhất 10.000đ.',
            'max_withdrawal_amount.gte' => 'Hạn mức rút tiền tối đa phải lớn hơn hoặc bằng mức tối thiểu.',
            'site_logo.image' => 'File logo phải là định dạng hình ảnh.',
            'site_logo.max' => 'Dung lượng logo tối đa 2MB.',
            'site_favicon.max' => 'Dung lượng favicon tối đa 1MB.',
        ];
    }
}
