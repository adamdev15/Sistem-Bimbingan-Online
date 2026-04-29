<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCustom extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Permintaan Atur Ulang Kata Sandi - Bimbel Jarimatrik')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pengguna') . '!')
            ->line('Kami mengirimkan email ini karena kami menerima permintaan untuk mengatur ulang kata sandi akun Anda di sistem Bimbel Jarimatrik.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan pengaturan ulang kata sandi ini akan kedaluwarsa dalam ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini. Keamanan akun Anda tetap terjaga.')
            ->salutation('Salam hormat,' . "\n" . 'Tim IT Bimbel Jarimatrik');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
