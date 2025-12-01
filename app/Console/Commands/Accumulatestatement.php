<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Accumulatestatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leavestatement:accumulate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This runs every beginning of a new month over all the leave statements represented';

    /**
     * Execute the console command.
     */
    public function handle(ileavetypeInterface $leavetyperepo, ileavestatementInterface $leavestatementrepo)
    {
        if(Carbon::now()->format('d')==1)
        {
            //Get the list of available leavetypes from the database
            $leavetypes=$leavetyperepo->getleavetypes();
            $leavetypeids=$leavetypes->map(function($leavetype, $index){
                return [
                    'id'=>$leavetype->id,
                    'name'=>$leavetype->name
                ];
            });

            /**
             * For each leave type loop through all the statements and roll over if 1st day of the month or year
             */
            $leavetypeids->map(function($leavetypedetail) use(&$leavetyperepo, &$leavestatementrepo){
                $leavetype=$leavetyperepo->getleavetype($leavetypedetail['id']);
                $leavestatementrepo->getleavestatementByLeaveType($leavetypedetail['id'])->each(function($userstatement) use (&$leavetype){
                    $userstatement->update([
                        'daysattained'=> $userstatement->daysattained + $leavetype->accumulation
                    ]);
                    $userstatement->save();  
                });
            });
            $this->info("Congradulations the Monthly accumulation completed successfully!!");
        }else
        {
            $this->info("Accumulation only done at the start of a new Month");
        }
    }
}
