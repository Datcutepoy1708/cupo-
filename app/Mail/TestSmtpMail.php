<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestSmtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fromEmail,
        public string $fromSenderName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromSenderName),
            subject: '[Cupo] Thư thử nghiệm kết nối SMTP thành công',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;">
                <h2 style="color: #c62828; margin-top: 0;">Xin chào từ Cupo!</h2>
                <p>Đây là bức thư điện tử thử nghiệm kiểm tra kết nối máy chủ gửi email (SMTP) từ hệ thống sàn thương mại điện tử <strong>Cupo</strong>.</p>
                <div style="background-color: #f1f8e9; border-left: 4px solid #4caf50; padding: 12px; margin: 20px 0;">
                    <strong style="color: #2e7d32;">✓ Kết nối SMTP hoạt động hoàn hảo!</strong>
                </div>
                <p style="color: #777; font-size: 13px;">Thời gian gửi: '.now()->format('H:i:s d/m/Y').'</p>
            </div>',
        );
    }
}
