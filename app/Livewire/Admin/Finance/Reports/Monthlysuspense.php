<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;

class Monthlysuspense extends Component
{
    protected $suspenseRepository;
    public $month;
    public $year;
    public function mount(){
        $this->month = date('m')-1;
        $this->year = date('Y');
    }
    public function boot(isuspenseInterface $suspenseRepository)
    {
        $this->suspenseRepository = $suspenseRepository;
    }
    public function getMonthlysuspensewallets(){
        return $this->suspenseRepository->getmonthlysuspensewallets($this->month,$this->year);
    }
    public function headers():array{
        return [
            ['key'=>'month','label'=>'Month','width'=>'15%'],
            ['key'=>'year','label'=>'Year','width'=>'15%'],
            ['key'=>'accountnumber','label'=>'Account Number','width'=>'15%'],
            ['key'=>'total_amount','label'=>'Total Amount','width'=>'15%'],
            ['key'=>'total_utilized','label'=>'Total Utilized','width'=>'15%'],
            ['key'=>'total_balance','label'=>'Total Balance','width'=>'15%'],
        ];
    }
    public function months():array{
        return [
            ['id'=>1,'name'=>'January'],
            ['id'=>2,'name'=>'February'],
            ['id'=>3,'name'=>'March'],
            ['id'=>4,'name'=>'April'],
            ['id'=>5,'name'=>'May'],
            ['id'=>6,'name'=>'June'],
            ['id'=>7,'name'=>'July'],
            ['id'=>8,'name'=>'August'],
            ['id'=>9,'name'=>'September'],
            ['id'=>10,'name'=>'October'],
            ['id'=>11,'name'=>'November'],
            ['id'=>12,'name'=>'December'],
        ];
    }
    public function years():array{
        return [
            ['id'=>2022,'name'=>'2022'],
            ['id'=>2023,'name'=>'2023'],
            ['id'=>2024,'name'=>'2024'],
            ['id'=>2025,'name'=>'2025'],
            ['id'=>2026,'name'=>'2026'],
            ['id'=>2027,'name'=>'2027'],
            ['id'=>2028,'name'=>'2028'],
            ['id'=>2029,'name'=>'2029'],
            ['id'=>2030,'name'=>'2030'],
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.reports.monthlysuspense',[
            'monthlysuspensewallets'=>$this->getMonthlysuspensewallets(),
            'headers'=>$this->headers(),
            'months'=>$this->months(),
            'years'=>$this->years(),
        ]);
    }
}
