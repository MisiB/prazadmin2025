<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\isuspenseInterface;
use Livewire\Component;
use Livewire\WithPagination;

class Suspensereports extends Component
{
    use WithPagination;
    protected $suspenseRepository;
    public $search=null;
    public function boot(isuspenseInterface $suspenseRepository)
    {
        $this->suspenseRepository = $suspenseRepository;
    }
    public function updatedSearch()
    {
        // No need to do anything. Livewire will re-render automatically.
        $this->resetPage();
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
    public function getrowsarray(){
        return $this->suspenseRepository->getpendingsuspensewalletsarray($this->search);
    }
    public function getrows(){
        return $this->suspenseRepository->getpendingsuspensewallets($this->search);
    }
    public function render()
    {
        
        $rows=$this->getrows();
        $rowsarray=$this->getrowsarray();
        return view('livewire.admin.finance.reports.suspensereports',[
            'headers'=>$this->headers(),
            'rows'=>$rows,
            'rowsarray'=>$rowsarray
        ]);
    }
}
 