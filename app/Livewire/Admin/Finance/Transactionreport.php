<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;
use Carbon\Carbon;
use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
class Transactionreport extends Component
{
    public $startdate = null;
    public $enddate = null;
    public $bankaccount = null;
    public $modal = false;
    protected  $bankaccountrepo;
    protected $banktransactionrepo;
    public $transactions ;
    public function boot(ibankaccountInterface $bankaccountrepo,ibanktransactionInterface $banktransactionrepo)
    {
        $this->bankaccountrepo = $bankaccountrepo;
        $this->banktransactionrepo = $banktransactionrepo;
    }
    public function mount()
    {
        $this->startdate = Carbon::now()->addDays(-7)->format('Y-m-d');
        $this->enddate = Carbon::now()->format('Y-m-d');
        $this->transactions = $this->banktransactionrepo->gettransactionbydaterange($this->startdate, $this->enddate, $this->bankaccount);
    }

    public function getBankAccounts()
    {
        return $this->bankaccountrepo->getbankaccounts();
    }
    public function retriverecords()
    {
        $this->validate(
            [
                'startdate' => 'required|date',
                'enddate' => 'required|date'
            ]
            
        );
        $data=$this->banktransactionrepo->gettransactionbydaterange(Carbon::parse($this->startdate)->format('Y-m-d'), Carbon::parse($this->enddate)->format('Y-m-d'), $this->bankaccount);
       
        $this->transactions = $data;
    }
    public function headers(): array
    {
        return [
            
            ['key' => 'transactiondate', 'label' => 'Transaction Date'],
            ['key' => 'sourcereference', 'label' => 'Source Reference'],
            ['key' => 'accountnumber', 'label' => 'Account Number'],
            ['key' => 'currency', 'label' => 'Currency'],
            ['key' => 'amount', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status']
        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.transactionreport',[
            'bankaccounts' => $this->getBankAccounts(),
            "headers" => $this->headers(),
            "transactions" => $this->transactions
        ]);
    }
   
}
