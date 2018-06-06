<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserPasswordChanged extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;

    protected $listen = [
        'Illuminate\Mail\Events\MessageSending' => [
            'App\Listeners\LogSendingMessage',
        ],
        'Illuminate\Mail\Events\MessageSent' => [
            'App\Listeners\LogSentMessage',
        ],
    ];
    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@epicschool.io','NO REPLY')
                    ->subject('[product] account password has been changed')
                    ->view('emails.UserPasswordChanged')
                    ->with(['first_name' => $this->data['first_name'],
                            'last_name' => $this->data['last_name'],
                            'email' => $this->data['email'],
                        ]);
    }
}
