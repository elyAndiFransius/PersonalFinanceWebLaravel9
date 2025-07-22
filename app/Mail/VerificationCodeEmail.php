<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerificationCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $code;


    public function __construct(User $user, $code)
    {
       $this->user = $user; 
       $this->code = $code;
    }

    public function build()
    {
        return $this->subject(' Kode OTP Kamu ')
              ->view('emails.verification')
              ->with([
                'name' => $this->user->name,
                'code' => $this->user->token_code
                
              ]);
    }

}
