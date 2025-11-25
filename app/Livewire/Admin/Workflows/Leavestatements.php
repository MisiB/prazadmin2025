<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\services\ileaverequestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Leavestatements extends Component
{
    use WithFileUploads, Toast;
    protected $leaverequestService;
    protected $userrepo;
    public $breadcrumbs = [];
    public $exportmodal = false;
    public $importmodal = false;
    public $leavetypeid;
    public $updatedexportfile;

    public function boot(ileaverequestService $leaverequestService)
    {
        $this->leaverequestService=$leaverequestService;
    }

    public function mount()
    {
        $this->breadcrumbs=[
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => "Leave Statements"]
        ];
    }

    public function headers(): array
    {
        return [
            ['label' => 'User', 'key' => 'username'],
            ['label' => 'Vacation Leave', 'key' => 'vacationleave'],
            ['label' => 'Annual Leave', 'key' => 'annualleave'],
            ['label' => 'Study Leave', 'key' => 'studyleave'],
            ['label' => 'Sick Leave', 'key' => 'sickleave'],
            ['label' => 'Maternity Leave', 'key' => 'maternityleave'],
            ['label' => 'Compassionate Leave', 'key' => 'compassionateleave'],
            ['label' => 'Year', 'key' => 'year']
        ];
    }
    public function getleavestatements()
    {
        $leavestatements = [];
        $this->leaverequestService->getusers()->each(function ($user) use (&$leavestatements) {

            $leavetypebalancedetails=[];
            $this->leaverequestService->getleavetypes()->each(function($leavetype) use (&$user,&$leavetypebalancedetails) {
                $statementdays=$this->leaverequestService->getleavestatementbyuserandleavetype($user->id, $leavetype->id)->days??0;
                $leavetypebalancedetails[] = [
                    'leavetype' => $leavetype->name,
                    'balance' => (float)$leavetype->ceiling - (float)$statementdays,
                ];
                
            });
            $keys = collect($leavetypebalancedetails)->pluck('leavetype')->all();
            $values = collect($leavetypebalancedetails)->pluck('balance')->all();
            $leavedetailmapping = array_combine($keys, $values);

            $leavestatements[]= [
                'username' => $user->name . ' ' . $user->surname,
                'leavetypes'=> $leavedetailmapping,
                'year'=>$this->leaverequestService->getleavestatementByUser($user->id)->first()->year??'-'
            ];
        });
        
        return $leavestatements;
    }

    public function import()
    {
        // Logic to handle the import of leave statements
        $this->validate([
            'leavetypeid' => 'required|numeric',
            'updatedexportfile' => 'required'
        ]);
        $filePath=$this->updatedexportfile->storeAs('leavestatements', $this->updatedexportfile->getClientOriginalName() );
        $importcsvdata=File::get(storage_path('app/private/'.$filePath));
        $importsarray=str_getcsv($importcsvdata, "\n");
        $importsarray=array_slice($importsarray,1);
        
        collect($importsarray)->each(function($statementcsvstr) {
            /**
             * 
             * ["0"=>"Employee Name","1"=>"Employee Surname", "2"=>"Employee Email" "3"=>"Leave Type", "4"=>"Year", "5"=>"Month", "6"=>"Utilized Days"];
             * 
             * */
            $statement=str_getcsv($statementcsvstr, ",");
            $user=$this->leaverequestService->getuserbyemail($statement[2]);
            $exists=$this->leaverequestService->getleavestatementbyuseridandleavename($user->id,$statement[3]);
            if(!$exists)
            {
                $response=$this->leaverequestService->createleavestatement([
                    'user_id' => $user->id,
                    'leavetype_id' => $this->leaverequestService->getleavetypebyname($statement[3])->id,
                    'year' => Carbon::now()->format('Y'),
                    'month'=> Carbon::now()->format('M'),
                    'days' => ($statement[6]!=null)?$statement[6]:0
                ]);
                $this->toast('success',$response['message']);
            }
            else
            {
                $response=$this->leaverequestService->updateleavestatement($exists->id, ['days' => $statement[6]]);
                $this->toast('success',$response['message']);
            } 
        });
        $this->importmodal = false;
    }

    public function export()
    {
        $this->validate([
            'leavetypeid' => 'required|numeric',
        ]);
        // Logic to handle the export of leave statements
        $statements[]=[
            "username"=>"Employee Name",
            "usersurname"=>"Employee Surname", 
            "useremail"=>"Employee Email", 
            "leavetypename"=>"Leave Type", 
            "year"=>"Year", 
            "month"=>"Month", 
            "days"=>"Utilized Days"
        ];
        //$this->userrepo->getall()->each(function($user) use (&$statements)
        $this->leaverequestService->getusers()->each(function($user) use (&$statements)
        {
            $exists=$this->leaverequestService->getleavestatementbyuserandleavetype($user->id, $this->leavetypeid);
           
            $statements[]=[
                'username' => $user->name,
                'usersurname'=> $user->surname,
                'useremail' => $user->email,
                'leavetypename' => $this->leaverequestService->getleavetype($this->leavetypeid)->name,
                'year' => Carbon::now()->format('Y'),
                'month' => Carbon::now()->format('m'),
                'days' =>  $exists ? $exists->days: ""
            ];
        });
        $filename=$this->leaverequestService->getleavetype($this->leavetypeid)->name.'_leave_statements.csv';
        $file=fopen($filename,'w');
        collect($statements)->each(function($statement) use (&$file)
        {
            fputcsv($file, $statement);
        });
        fclose($file);
        $this->exportmodal = false;
        return response()->download(public_path($filename))->deleteFileAfterSend(true);

    }

    public function render()
    {
        return view('livewire.admin.workflows.leavestatements',[
            'leavestatements'=>$this->getleavestatements(),
            'headers'=> $this->headers()
        ]);
    }
}