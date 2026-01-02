<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class Updatepostedinvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:updatepostedinvoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $ids = DB::table('invoiceposted')->get();
         foreach ($ids as $id) {
            $invoice = DB::table('invoices')->where('id', $id->id)->first();
            if ($invoice) {
                $invoice->posted = 1;
                $invoice->save();
                $this->info('Invoice '.$invoice->id.' posted');
            } else {
                $this->error('Invoice '.$id->id.' not found');
            }
         }
    }
}
