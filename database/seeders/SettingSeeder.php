<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // 1. General Settings
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Cupo — Sàn Thương Mại Điện Tử',
                'type' => 'text',
            ],
            [
                'group' => 'general',
                'key' => 'site_tagline',
                'value' => 'Mua sắm thông minh, ngập tràn ưu đãi',
                'type' => 'text',
            ],
            [
                'group' => 'general',
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'general',
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'general',
                'key' => 'contact_phone',
                'value' => '1900 8888',
                'type' => 'text',
            ],
            [
                'group' => 'general',
                'key' => 'contact_email',
                'value' => 'support@cupo.vn',
                'type' => 'text',
            ],
            [
                'group' => 'general',
                'key' => 'contact_address',
                'value' => 'Tầng 12, Tòa nhà Cupo Tower, Cầu Giấy, Hà Nội',
                'type' => 'text',
            ],
            [
                'group' => 'general',
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
            ],
            [
                'group' => 'general',
                'key' => 'maintenance_message',
                'value' => 'Hệ thống đang bảo trì nâng cấp để phục vụ bạn tốt hơn. Vui lòng quay lại sau ít phút!',
                'type' => 'text',
            ],

            // 2. Seller & Commission Settings
            [
                'group' => 'seller',
                'key' => 'default_commission_rate',
                'value' => '10',
                'type' => 'number',
            ],
            [
                'group' => 'seller',
                'key' => 'auto_approve_sellers',
                'value' => '0',
                'type' => 'boolean',
            ],
            [
                'group' => 'seller',
                'key' => 'min_withdrawal_amount',
                'value' => '50000',
                'type' => 'number',
            ],
            [
                'group' => 'seller',
                'key' => 'max_withdrawal_amount',
                'value' => '50000000',
                'type' => 'number',
            ],
            [
                'group' => 'seller',
                'key' => 'escrow_holding_days',
                'value' => '3',
                'type' => 'number',
            ],

            // 3. Order & Shipping Settings
            [
                'group' => 'order',
                'key' => 'default_shipping_fee',
                'value' => '30000',
                'type' => 'number',
            ],
            [
                'group' => 'order',
                'key' => 'free_shipping_threshold',
                'value' => '300000',
                'type' => 'number',
            ],
            [
                'group' => 'order',
                'key' => 'auto_cancel_pending_hours',
                'value' => '48',
                'type' => 'number',
            ],
            [
                'group' => 'order',
                'key' => 'enable_cod',
                'value' => '1',
                'type' => 'boolean',
            ],

            // 4. Payment Gateway Settings
            [
                'group' => 'payment',
                'key' => 'enable_vnpay',
                'value' => '1',
                'type' => 'boolean',
            ],
            [
                'group' => 'payment',
                'key' => 'vnpay_tmn_code',
                'value' => 'CUPOVN01',
                'type' => 'text',
            ],
            [
                'group' => 'payment',
                'key' => 'vnpay_hash_secret',
                'value' => 'SECRETKEYDEMOVNPAYCUPO123456789',
                'type' => 'text',
            ],
            [
                'group' => 'payment',
                'key' => 'vnpay_sandbox',
                'value' => '1',
                'type' => 'boolean',
            ],
            [
                'group' => 'payment',
                'key' => 'enable_momo',
                'value' => '1',
                'type' => 'boolean',
            ],
            [
                'group' => 'payment',
                'key' => 'momo_partner_code',
                'value' => 'MOMOCUPO01',
                'type' => 'text',
            ],
            [
                'group' => 'payment',
                'key' => 'momo_access_key',
                'value' => 'ACCESSKEYDEMOMOMO123',
                'type' => 'text',
            ],
            [
                'group' => 'payment',
                'key' => 'momo_secret_key',
                'value' => 'SECRETKEYDEMOMOMOCUPO456',
                'type' => 'text',
            ],
            [
                'group' => 'payment',
                'key' => 'momo_sandbox',
                'value' => '1',
                'type' => 'boolean',
            ],

            // 5. Mail Settings
            [
                'group' => 'mail',
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_host',
                'value' => 'smtp.mailgun.org',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_port',
                'value' => '587',
                'type' => 'number',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_username',
                'value' => 'postmaster@cupo.vn',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_password',
                'value' => '',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_address',
                'value' => 'noreply@cupo.vn',
                'type' => 'text',
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_name',
                'value' => 'Cupo Marketplace',
                'type' => 'text',
            ],

            // 6. SEO & Social Settings
            [
                'group' => 'seo',
                'key' => 'meta_title',
                'value' => 'Cupo — Sàn Thương Mại Điện Tử Uy Tín & Ưu Đãi Hàng Đầu',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'meta_description',
                'value' => 'Mua sắm hàng triệu sản phẩm giá tốt, giao nhanh 2h, bảo vệ người mua an tâm cùng Cupo.',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'meta_keywords',
                'value' => 'cupo, san thuong mai dien tu, mua sam online, flash sale, voucher giam gia',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'og_image',
                'value' => null,
                'type' => 'image',
            ],
            [
                'group' => 'seo',
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/cupo.vietnam',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'social_tiktok',
                'value' => 'https://tiktok.com/@cupo.official',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/@cupovietnam',
                'type' => 'text',
            ],
            [
                'group' => 'seo',
                'key' => 'social_zalo',
                'value' => 'https://zalo.me/cupo',
                'type' => 'text',
            ],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                $s
            );
        }

        Cache::forget('cupo_settings');
    }
}
