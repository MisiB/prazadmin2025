<?php

namespace App\Livewire\Admin\Trackers;

use App\Interfaces\services\ischoolService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Returnsoverview extends Component
{

    use Toast, WithPagination;

    protected $schoolService;
    protected string $pageName= "returnsoverviewpage";
    public $itemsperpage=10;
    public $record;
    public $schoolexpensecategory;
    public $schoolexpensecategories;
    public $openschoolmodal=false;
    public $viewexpendituremodal=false;
    public $currentmonthlyreturn;
    public $currentschool=null;
    public $schoolname;
    public $schoolid;
    public $year;
    public $month;
    public $approvedreturnstatus;
    public $pendingreturnstatus;

    public function boot(ischoolService $schoolService)
    {
        $this->schoolService = $schoolService;
        $this->approvedreturnstatus="APPROVED";
        $this->pendingreturnstatus="PENDING";
    }

    public function mount()
    {
        $this->schoolexpensecategories = $this->getschoolexpesnsecategories()??[];
        $this->year = $this->schoolService->year;
        $this->month = $this->schoolService->month;
        $this->schoolname=null;
        $this->schoolid=null;
    }

    public function updatedPageName()
    {
        $this->resetPage($this->pageName);
    }
    public function updatedReturnsdatapageName()
    {
        $this->resetPage($this->returnsdatapageName);
    }
    public function getschooltotalapprovedexpenditure()
    {
        if($this->currentschool !==null)
        {   
            return $this->schoolService->gettotalexpenditure($this->currentschool->school_number,$this->approvedreturnstatus,  $this->year, $this->month);
        }
        return [];
    }

    public function getschooltotalpendingexpenditure()
    {
        if($this->currentschool !==null)
        {   
            return $this->schoolService->gettotalexpenditure($this->currentschool->school_number,$this->pendingreturnstatus,  $this->year, $this->month);
        }
        return [];
    }

    public function getschoolexpesnsecategories()
    {
        return $this->schoolService->getschoolexpensecategories();
    }
    
    public function closeschoolmodal()
    {  
        $this->openschoolmodal=false;
    }

    public function openviewexpendituremodal($monthlyreturnid)
    {
        $this->currentmonthlyreturn=$this->schoolService->getmonthlyreturnbyid($monthlyreturnid);
        $this->viewexpendituremodal=true;
    }


    public function searchschool()
    {
        if($this->schoolname==null && $this->schoolid==null)
        {
            $this->toast('warning','Please provide School Name or School ID to search.');
            return;
        }
        $this->validate([
            'schoolname' => 'sometimes',
            'schoolid' => 'sometimes',
        ]);
        $this->currentschool =  $this->schoolService->searchschool( $this->schoolname, $this->schoolid);
        return is_null($this->currentschool) ? $this->toast('warning', 'School not registered in the system'): $this->toast('success', 'Registered school found') ;    
    }

    public function backtoschoolsearch()
    {
        $this->currentschool=null;
        $this->redirect(route('admin.trackers.returnsoverview'));
    }

    public function exportexcelreport()
    {
        return $this->schoolService->exportexcelreport(
            $this->currentschool!=null ? $this->schoolService->getmonthlyreturns($this->currentschool->school_number, "APPROVED", $this->year, $this->month): [],
            $this->currentschool->name
        );
    }

    

    public function render()
    {
        return view('livewire.admin.trackers.returnsoverview', [
            'monthlist' => $this->schoolService->getmonthlist(),
            'headers' => $this->schoolService->getheaders(),
            'monthlyreturnheaders'=>  $this->schoolService->monthlyreturnheaders(),
            'monthlyreturns' =>$this->currentschool!=null ? $this->schoolService->getmonthlyreturns($this->currentschool->school_number, "APPROVED", $this->year, $this->month, $this->itemsperpage) : new LengthAwarePaginator([],0,2),
            'monthlyreturndata'=>$this->currentmonthlyreturn!=null ?  $this->schoolService->getmonthlyreturndatabyreturnid($this->currentmonthlyreturn->id, $this->itemsperpage): new LengthAwarePaginator([],0,2),
            'sourceoffunds' => $this->schoolService->getsourceoffunds(),
            'currencies' => $this->schoolService->getcurrenciesbystatus("ACTIVE"),
            'totalpendingreturns'=>$this->currentschool!=null ? $this->schoolService->getmonthlyreturns($this->currentschool->school_number,"PENDING" ,$this->year, $this->month)->count(): 0,
            'totalpendingexpenditure'=>$this->getschooltotalpendingexpenditure(),
            'totalapprovedreturns'=>$this->currentschool!=null ? $this->schoolService->getmonthlyreturns($this->currentschool->school_number,"APPROVED" ,$this->year, $this->month)->count(): 0,
            'totalapprovedexpenditure'=>$this->getschooltotalapprovedexpenditure(),
            
            
        ]);
    }
}  
