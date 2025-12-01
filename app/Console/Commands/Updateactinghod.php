<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\ileaverequestService;
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
    protected $signature = 'leavestatement:updateactinghod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ideally this command should run daily in the morning to update HOD in case of return from leave';
    /**
     * Execute the console command. For the pending requests
     */
    public function handle(ileaverequestInterface $leaverequestrepo, iuserInterface $userrepo, ileaverequestService $leaverequestService)
    {
        $leaverequestrepo->getleaverequestByStatus(['A','P'])->each(function($requestrecord) use($userrepo, $leaverequestService){
            $currentDate=Carbon::now()->format('Y-m-d');
            if($currentDate === $requestrecord->returndate && $requestrecord->actinghod_id != null){
                $acting_hod=$userrepo->getuser($requestrecord->actinghod_id);
                if($acting_hod->hasRole('Acting HOD'))
                {
                    $acting_hod->removeRole('Acting HOD');//'Acting HOD' Role
                }
                $hod=$userrepo->getuser($requestrecord->user_id);
                $hod->notify(new LeaverequestSubmitted($leaverequestService, $requestrecord->uuid ));
            }
            $this->info('Leave updates executed');
        });
        $this->info("Acting HODs updated executed");
    }
}
