<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;

class Piechart extends Component
{
    public $accounumber;
    public $totalclaimed;
    public $totalpending;
    public $totalblocked;

    public array $myChart = [];
    public function mount()
    {
        $this->myChart = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Claimed', 'Pending', 'Blocked'], 
                'datasets' => [
                    [
                        'label' => 'Total Transactions',
                        'data' => [$this->totalclaimed, $this->totalpending, $this->totalblocked],
                    ]
                ]
            ]
        ];
    }
    

    public function render()
    {
        return view('livewire.admin.finance.piechart');
    }
}
