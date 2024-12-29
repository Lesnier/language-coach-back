<?php

namespace App\Jobs;

use App\Mail\AgendaReminder;
use App\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAgendaReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $agenda;
    public function __construct(Agenda $agenda)
    {
        $this->agenda = $agenda;
    }

    public function handle()
    {
        $user = $this->agenda->user;

        $reminderTime = $this->agenda->time->setTime($this->agenda->time->format('H'),0,0)->subHour();
        $cubaNow = Carbon::now()->setTimezone('America/Havana');

        if ($reminderTime <= $cubaNow)
        {
            Mail::to($user->email)->send(new AgendaReminder($this->agenda));
        }
    }
}
