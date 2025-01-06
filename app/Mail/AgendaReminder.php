<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgendaReminder extends Mailable
{
    public $emailBody;
    use Queueable, SerializesModels;
    public function __construct($emailBody)
    {
        $this->emailBody = $emailBody;
    }

    public function build()
    {
        return $this->view('mail.agenda_confirmation');
    }
}
