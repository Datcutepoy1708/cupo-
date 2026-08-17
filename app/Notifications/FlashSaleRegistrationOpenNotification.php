<?php

namespace App\Notifications;

use App\Models\FlashSale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FlashSaleRegistrationOpenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FlashSale $flashSale) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->flashSale->registration_deadline
            ? $this->flashSale->registration_deadline->format('d/m/Y H:i')
            : 'chua xac dinh';

        $startsAt = $this->flashSale->starts_at
            ? $this->flashSale->starts_at->format('d/m/Y H:i')
            : 'chua xac dinh';

        return (new MailMessage)
            ->subject('[Cupo] Mo dang ky Flash Sale: '.$this->flashSale->name)
            ->greeting('Xin chao '.$notifiable->name.',')
            ->line('Phien Flash Sale "'.$this->flashSale->name.'" dang mo nhan dang ky san pham.')
            ->line('Thoi gian phien dien ra: '.$startsAt)
            ->line('Han chot dang ky: '.$deadline)
            ->action('Dang ky ngay', url('/seller/flash-sale-registrations'))
            ->line('Dang ky som de duoc Admin xet duyet va chen san pham cua ban vao phien Flash Sale nay.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'flash_sale_registration_open',
            'flash_sale_id' => $this->flashSale->id,
            'flash_sale_name' => $this->flashSale->name,
            'registration_deadline' => $this->flashSale->registration_deadline?->toISOString(),
            'starts_at' => $this->flashSale->starts_at?->toISOString(),
        ];
    }
}
