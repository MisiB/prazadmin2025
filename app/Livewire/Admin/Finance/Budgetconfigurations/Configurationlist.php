<?php

namespace App\Livewire\Admin\Finance\Budgetconfigurations;

use App\Interfaces\repositories\ibudgetconfigurationInterface;
use Livewire\Component;

class Configurationlist extends Component
{ 
    public $selectedTab = "expensecategory-tab";
    public $breadcrumbs;
    protected $repo;
    public function boot(ibudgetconfigurationInterface $repo)
    {
        $this->repo = $repo;
   
    }
    public function mount(  )
    {
        $this->breadcrumbs = [
            [
                "label" => "Dashboard",
                "link" => route("admin.home"),
            ],
            [
                "label" => "Budget Configurations"
            ],
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.budgetconfigurations.configurationlist');
    }
}
