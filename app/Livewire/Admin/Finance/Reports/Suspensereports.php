<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Suspensereports extends Component
{
    use WithPagination;
    
    protected $suspenseRepository;

    public function boot(isuspenseInterface $suspenseRepository): void
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

    public function rows(): LengthAwarePaginator
    {
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
