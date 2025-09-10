<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class VerifyEmail extends Mailable
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
        return $this->subject('Verify Your Email')
            ->markdown('emails.verify')
            ->with([
                'user' => $this->user,
                'url'  => $this->url,
            ]);
    }

    public function sendVerifyMail(User $user)
    {
        $url = url('/verify-email?token=' . $user->verification_token);

        Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return response()->json(['message' => 'Verification email sent!']);
    }
}
