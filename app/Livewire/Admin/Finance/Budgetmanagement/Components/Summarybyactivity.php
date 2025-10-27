<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use App\Interfaces\repositories\ibudgetInterface;
use Livewire\Component;

class Summarybyactivity extends Component
{
    public $budget;
    public function mount($budget)
    {
        $this->budget = $budget;
    }
    public function summarybyoutput(){
        return $this->budget->budgetitems->groupBy('strategysubprogrammeoutput_id');
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.summarybyactivity',[
            "summarybyoutput" => $this->summarybyoutput()
        ]);
    }
}
