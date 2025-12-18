<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Rolloverstatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leavestatement:rollover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This runs every beginning of the year over all the leave statements represented';

    /**
     * Execute the console command.
     */
    public function handle(ileavetypeInterface $leavetyperepo, ileavestatementInterface $leavestatementrepo)
    {
        /*Rollover only happens on the first day of the year*/ 
        if( Carbon::now()->format('d')===1 && Carbon::now()->format('m')===1)
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
                if($leavetype->rollover==='Y')
                {
                    //find all related statements and add their accumulation to the available days as long as the available days are less than the ceiling
                    $leavestatementrepo->getleavestatementByLeaveType($leavetypedetail['id'])->each(function($userstatement) use (&$leavetype){
                        if($userstatement->daysattained < $leavetype->ceiling)
                        {
                            $attaineddaysupdate=$userstatement->daysattained + $leavetype->accumulation;
                            $userstatement->update([
                                'daysattained'=> ($attaineddaysupdate<$leavetype->ceiling) ? $attaineddaysupdate:$leavetype->ceiling,
                                'daystaken'=>0
                            ]);
                            $userstatement->save();
                        }else{
                            $userstatement->update([
                                'daysattained'=> $leavetype->ceiling,
                                'daystaken'=>0
                            ]);
                            $userstatement->save();
                        }  
                    });
                }else{
                    $leavestatementrepo->getleavestatementByLeaveType($leavetypedetail['id'])->each(function($userstatement){
                        $userstatement->update([
                            'daysattained'=> 0,
                            'daystaken'=>0
                        ]);
                        $userstatement->save();   
                    });
                }
            });
            $this->info("Congradulations the Yearly rollover completed successfully!!");
        }else
        {
            $this->info("Rollover only done at the start of a new Year");
        }
    }
}
