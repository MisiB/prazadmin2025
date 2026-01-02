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
        $currentdate=2;
        $firstmonth=1;
        //date should be 1
        if(Carbon::now()->format('d')==$currentdate)
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
            $leavetypeids->map(function($leavetypedetail) use(&$leavetyperepo, &$leavestatementrepo, &$currentdate, &$firstmonth){

                $leavetype=$leavetyperepo->getleavetype($leavetypedetail['id']);
                //If month is January and leave type is rollover do accumulate statement
                if(Carbon::now()->format('m')==$firstmonth && $leavetype->rollover==='Y')
                {
                    $this->info("Its January rollover comes first before the acumulation. Hence accumulation for ".$leavetype->name." leaves is done on rollover.");
                }else{
                    $leavestatementrepo->getleavestatementByLeaveType($leavetypedetail['id'])->each(function($userstatement) use (&$leavetype, &$firstmonth){

                        //If Month is January accumulate the Sick and Maternity Leave statements to ceiling values
                        if(Carbon::now()->format('m')==$firstmonth && in_array($leavetype->name, ['Sick', 'Maternity']))
                        {
                            $userstatement->update([
                                'daysattained'=> $leavetype->ceiling
                            ]);
                            $userstatement->save();     
                        }else{
                            //Normal accumulation
                            $userstatement->update([
                                'daysattained'=> $userstatement->daysattained + $leavetype->accumulation
                            ]);
                            $userstatement->save(); 
                        } 
                    });           
                }
            });
            $this->info("Congradulations the Monthly accumulation completed successfully!!");
        }else
        {
            $this->info("Accumulation only done at the start of a new Month");
        }
    }
}
