<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Notifications\LeaverequestSubmitted;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Updateactinghod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:updateactinghod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $hodrole='Acting HOD';
    /**
     * Execute the console command. For the pending requests
     */
    public function handle(ileaverequestInterface $leaverequestrepo, iuserInterface $userrepo, ileavetypeInterface $leavetyperepo, ileaverequestapprovalInterface $leaverequestapprovalrepo)
    {
        $leaverequestrepo->getleaverequestByStatus('P')->each(function($requestrecord) use($userrepo, $leavetyperepo, $leaverequestapprovalrepo, &$hodrole){
            $currentDate=Carbon::now()->format('Y-m-d');
            if($currentDate === $requestrecord->returndate && $requestrecord->actinghod_id != null){
                $acting_hod=$userrepo->getuser($requestrecord->actinghod_id);
                $acting_hod->removeRole($hodrole);//'Acting HOD' Role
                $hod=$userrepo->getuser($requestrecord->user_id);
                //$hod->notify(new LeaverequestSubmitted($requestrecord, $leavetyperepo, $leaverequestapprovalrepo ));
            }
        });
    }
}
