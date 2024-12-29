<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgendaReminder extends Mailable
{
    public $agenda;
    use Queueable, SerializesModels;
    public function __construct($agenda)
    {
        $this->agenda = $agenda;
    }

    public function build()
    {
        return $this->view('mail.agenda_reminder');
    }
}
