<?php

namespace Backpack\CRUD\app\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable, $email = null)
    {
        $email = $email ?? $notifiable->getEmailForPasswordReset();

        return (new MailMessage())
            ->subject(trans('tannhatcms::base.password_reset.subject'))
            ->greeting(trans('tannhatcms::base.password_reset.greeting'))
            ->line([
                trans('tannhatcms::base.password_reset.line_1'),
                trans('tannhatcms::base.password_reset.line_2'),
            ])
            ->action(trans('tannhatcms::base.password_reset.button'), route('backpack.auth.password.reset.token', $this->token).'?email='.urlencode($email))
            ->line(trans('tannhatcms::base.password_reset.notice'));
    }
}
