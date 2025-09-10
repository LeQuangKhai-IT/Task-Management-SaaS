<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
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
        return $this->subject('Verify your email for Trello')
            ->markdown('emails.verify')
            ->with([
                'user' => $this->user,
                'url'  => $this->url,
            ]);
    }
}
