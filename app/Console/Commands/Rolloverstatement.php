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
    protected $signature = 'app:statementsrollover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This runs monthly to command rolls over all the leave statements represented';

    /**
     * Execute the console command.
     */
    public function handle(ileavetypeInterface $leavetyperepo, ileavestatementInterface $leavestatementrepo)
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
                    if($userstatement->days<$leavetype->ceiling)
                    {
                        $userstatement->update([
                            'days'=> $userstatement->days + $leavetype->accumulation
                        ]);
                        $userstatement->save();
                    }else{
                        $userstatement->update([
                            'days'=> $leavetype->ceiling
                        ]);
                        $userstatement->save();
                    }  
                });
            }else{
                $leavestatementrepo->getleavestatementByLeaveType($leavetypedetail['id'])->each(function($userstatement) use (&$leavetype){
                    if(Carbon::now()->format('m')==1 && Carbon::now()->format('d')==1)
                    {
                        
                        $userstatement->update([
                            'days'=> 0
                        ]);
                        $userstatement->save();
                    }else{
                        $userstatement->update([
                            'days'=> $userstatement->days + $leavetype->accumulation
                        ]);
                        $userstatement->save();
                    }    
                });
            }
        });
    }
}
