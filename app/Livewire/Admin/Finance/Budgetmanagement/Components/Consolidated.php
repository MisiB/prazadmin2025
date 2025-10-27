<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use App\Interfaces\repositories\idepartmentInterface;
use Livewire\Component;

class Consolidated extends Component
{
    public $budget;
    public $department_id;
    protected $departmentrepo;
    public $budgetitems;
    public $totalbudget =0;
    public $totalutilized =0;
    public $totalremaining =0;
    public function boot(idepartmentInterface $departmentrepo)
    {
        $this->departmentrepo = $departmentrepo;
    }
    public function departments(){
        return $this->departmentrepo->getdepartments();
    }
    public function mount($budget)
    {
        $this->budget = $budget;
        $this->budgetitems = $this->budget->budgetitems->where('status','APPROVED');
    }

    public function headers(){

        return [
            ['key'=>'activity','label'=>'Activity'],
            ['key'=>'strategysubprogrammeoutput.output','label'=>'Output'],
            ['key'=>'department.name','label'=>'Department'],
            ['key'=>'expensecategory.name','label'=>'Expense Category'],
            ['key'=>'sourceoffund.name','label'=>'Source of Funds'],
            ['key'=>'quantity','label'=>'Quantity'],
            ['key'=>'unitprice','label'=>'Unit Price'],
            ['key'=>'total','label'=>'Total'],
            ['key'=>'utilized','label'=>'Utilized'],
            ['key'=>'remaining','label'=>'Remaining'],
            ['key'=>'status','label'=>'Status'],
            ['key'=>'action','label'=>'']
        ];
    }
    public function updatedDepartmentId(){
        $this->budgetitems = $this->budget->budgetitems->where('department_id', $this->department_id);
    }
    public function computetotals(){
        $budgetitems = $this->budgetitems;
        $this->totalbudget = $budgetitems->sum("total");
        $this->totalutilized = 0;
        $this->totalremaining = $this->totalbudget-$this->totalutilized;
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.consolidated', [
            'headers' => $this->headers(),
            'departments' => $this->departments(),
            'budgetitems' => $this->budgetitems,
            'summary' => $this->computetotals()
                    ]);
    }
}
