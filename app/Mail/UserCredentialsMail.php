<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $plainPassword)
    {
    }

    public function build()
    {
        return $this->subject('Your account has been created')
            ->view('emails.user_credentials')
            ->with([
                'name' => $this->user->name,
                'email' => $this->user->email,
                'password' => $this->plainPassword,
                'loginUrl' => route('login'),
                'settingsUrl' => route('settings'),
            ]);
    }
}

