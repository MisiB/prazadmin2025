<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use Livewire\Component;

class Summarybydepartment extends Component
{
    public $budget;
    public function mount($budget)
    {
        $this->budget = $budget;
    }
    public function summarybydepartment(){
        return $this->budget->budgetitems->groupBy('department_id');
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.summarybydepartment', [
            'summarybydepartment' => $this->summarybydepartment(),
        ]);
    }
}
