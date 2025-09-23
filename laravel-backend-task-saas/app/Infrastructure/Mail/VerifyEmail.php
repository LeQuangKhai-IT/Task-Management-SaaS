<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $url;

    public function __construct(string $email, string $url)
    {
        $this->email = $email;
        $this->url  = $url;
    }

    public function build()
    {
        return $this->subject('Verify your email for Trello')
            ->markdown('emails.verify')
            ->with([
                'email' => $this->email,
                'url'  => $this->url,
            ]);
    }
}
