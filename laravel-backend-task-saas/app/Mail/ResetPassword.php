<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $url;

    public function __construct(User $user, string $url)
    {
        $this->user = $user;
        $this->url  = $url;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
            ->markdown('emails.reset-password')
            ->with([
                'user' => $this->user,
                'url'  => $this->url,
            ]);
    }

    public function sendResetPasswordMail(User $user)
    {
        $url = url('/reset-password?token=' . $user->reset_token);

        Mail::to($user->email)->send(new ResetPassword($user, $url));

        return response()->json(['message' => 'Password reset email sent!']);
    }
}
