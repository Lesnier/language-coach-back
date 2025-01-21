<?php

namespace App\Console\Commands;

use App\Mail\AgendaReminder;
use App\Mail\BillNotification;
use App\Models\Bill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificateBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificate:bills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for make bill notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bills = Bill::with('subscription','payment','user')->get();
        foreach($bills as $bill){
            if(!$bill->payment && $bill->status == "Not paid")
            {
                Mail::to($bill->user->email)->send(new BillNotification("You have a bill to pay."));
                $this->info("Email send to " . $bill->user->name);
            }
            if($bill->payment && $bill->status == "Not paid")
            {
                $bill->status = "Revision";
                $bill->save();
                $this->info("Bills status changed to revision for " . $bill->user->name);
            }
            if($bill->payment && $bill->status == "Paid" && !$bill->notified)
            {
                $bill->notified = true;
                $bill->save();
                Mail::to($bill->user->email)->send(new BillNotification("Payment validated"));
                $this->info("Bill notification status changed for " . $bill->user->email);
            }
        }
        return 0;
    }
}
