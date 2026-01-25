<?php

namespace App\Notifications\Tenant;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        /** Tenant $tenant */
        $tenant = app('tenant');
        $subdomain = $tenant ? $tenant->domain : request()->route('subdomain');

        $url = route('tenant.password.reset', [
            'subdomain' => $subdomain,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage())
            ->subject('Redefinição de Senha')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line(
                'Você está recebendo este e-mail porque recebemos uma solicitação ' .
                'de redefinição de senha para sua conta.'
            )
            ->action('Redefinir Senha', $url)
            ->line(
                'Este link de redefinição de senha expirará em ' .
                config('auth.passwords.users.expire') . ' minutos.'
            )
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.')
            ->salutation('Atenciosamente, Equipe ' . config('app.name'));
    }
}
