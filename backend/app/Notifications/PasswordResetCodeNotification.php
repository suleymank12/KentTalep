<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Şifre sıfırlama kodunu e-posta ile gönderir. Bilinçli olarak kuyruğa
 * alınmaz (ShouldQueue yok): mobil istemci kodu hemen beklediği için
 * gönderim senkron yapılır (bkz. ADR-13).
 */
final class PasswordResetCodeNotification extends Notification
{
    public function __construct(private readonly string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('KentTalep şifre sıfırlama kodu')
            ->greeting('Merhaba,')
            ->line('Şifre sıfırlama kodunuz: '.$this->code)
            ->line('Bu kod 15 dakika boyunca geçerlidir.')
            ->line('Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz.');
    }
}
