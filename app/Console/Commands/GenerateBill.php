<?php

namespace App\Console\Commands;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:bills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for bills generation in the actual month';

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
        if(Bill::isStartMont()){
             $this->info(Bill::generateBill());
        }
        return 0;
    }
}
