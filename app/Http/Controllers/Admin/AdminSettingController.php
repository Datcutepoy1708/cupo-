<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestMailRequest;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Mail\TestSmtpMail;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * GET /admin/settings
     * Trang quan ly cai dat he thong (6 nhom tabs)
     */
    public function index(Request $request): View|JsonResponse
    {
        $settings = $this->settingService->all();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * POST /admin/settings/update
     * Luu tat ca cai dat va upload logo/favicon/og_image
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->except(['_token', 'site_logo', 'site_favicon', 'og_image']);
        $files = $request->only(['site_logo', 'site_favicon', 'og_image']);

        // Set default 0 cho cac switch checkbox neu khong co trong request
        $booleanKeys = [
            'maintenance_mode',
            'auto_approve_sellers',
            'enable_cod',
            'enable_vnpay',
            'vnpay_sandbox',
            'enable_momo',
            'momo_sandbox',
            'bct_registered',
            'dmca_protected',
        ];

        // Xu ly cac boolean keys cua tab hien tai dang submit
        foreach ($booleanKeys as $bKey) {
            if ($request->has('_tab_'.$bKey) && ! array_key_exists($bKey, $data)) {
                $data[$bKey] = '0';
            }
        }

        $this->settingService->updateMany($data, $files);

        // Ghi nhat ky kiem toan he thong
        ActivityLogService::log(
            'update_settings',
            'settings',
            'Cập nhật cấu hình hệ thống sàn',
            null,
            ['updated_keys' => array_keys($data)]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lưu cài đặt hệ thống thành công!',
                'data' => $this->settingService->all(),
            ]);
        }

        return redirect()->back()->with('success', 'Lưu cài đặt hệ thống thành công!');
    }

    /**
     * POST /admin/settings/test-mail
     * Thu gui email test qua SMTP vua cau hinh
     */
    public function testMail(TestMailRequest $request): JsonResponse
    {
        $toEmail = $request->input('test_email');

        try {
            // Nhanh chong ap dung cau hinh mail moi nhat neu duoc luu trong settings
            $mailer = $this->settingService->get('mail_mailer', config('mail.default'));
            $host = $this->settingService->get('mail_host', config('mail.mailers.smtp.host'));
            $port = $this->settingService->get('mail_port', config('mail.mailers.smtp.port'));
            $username = $this->settingService->get('mail_username', config('mail.mailers.smtp.username'));
            $password = $this->settingService->get('mail_password', config('mail.mailers.smtp.password'));
            $encryption = $this->settingService->get('mail_encryption', config('mail.mailers.smtp.encryption'));
            $fromAddress = $this->settingService->get('mail_from_address', config('mail.from.address'));
            $fromName = $this->settingService->get('mail_from_name', config('mail.from.name'));

            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);

            Mail::to($toEmail)->send(new TestSmtpMail($fromAddress, $fromName));

            return response()->json([
                'success' => true,
                'message' => "Đã gửi email thử nghiệm thành công tới địa chỉ: {$toEmail}",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối máy chủ gửi mail: '.$e->getMessage(),
            ], 422);
        }
    }
}
