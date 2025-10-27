<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;

class Suspensereports extends Component
{
    protected $suspenseRepository;
    public function boot(isuspenseInterface $suspenseRepository)
    {
        $this->suspenseRepository = $suspenseRepository;
    }
    public function headers():array{
        return [
            ['key'=>'created_at','label'=>'Created At','width'=>'15%'],
            ['key'=>'last_updated_at','label'=>'Last Updated At'],
            ['key'=>'customer_name','label'=>'Customer Name'],
            ['key'=>'accountnumber','label'=>'Account Number'],
            ['key'=>'amount','label'=>'Amount'],
            ['key'=>'balance','label'=>'Balance'],
        ];
    }
    public function rows():array{
        return $this->suspenseRepository->getpendingsuspensewallets();
    }
    public function render()
    {
        return view('livewire.admin.finance.reports.suspensereports',[
            'headers'=>$this->headers(),
            'rows'=>$this->rows()
        ]);
    }
}
