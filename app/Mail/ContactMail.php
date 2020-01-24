<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageText;
    public $fromMail;
    public $clientName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $from, $message)
    {
        //
        $this->clientName = $name;
        $this->fromMail = $from;
        $this->messageText = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->fromMail)->view('emails.contact')->with([
            'name'=>$this->clientName,
            'message'=>$this->messageText
        ]);
    }
}
