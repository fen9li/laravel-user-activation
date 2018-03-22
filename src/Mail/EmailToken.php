<?php

namespace Fen9li\LaravelUserActivation\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use voicelib\User;

class EmailToken extends Mailable
{
    use Queueable, SerializesModels;
 
    public $user;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user,String $token)
    {
        //
        $this->user=$user;
        $this->token=$token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('userActivation.uaemail.subject'))
                    ->from(config('userActivation.uaemail.from'))
                    ->replyTo(config('userActivation.uaemail.replyTo'))
                    ->view('laravel-user-activation::email')
                    ->with([
                            'email'=>$this->user->email,
                            'token'=>$this->token,
                           ]);
    }
}
