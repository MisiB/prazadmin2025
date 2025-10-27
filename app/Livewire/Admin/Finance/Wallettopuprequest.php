<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\ibanktransactionInterface;
use App\Interfaces\repositories\iwallettopupInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Wallettopuprequest extends Component
{
    use Toast;
    public $year;
    public $status;
    public $reason;
    public $breadcrumbs=[];
    protected  $wallettoprepo;
    protected $banktransactionrepo;
    public $selectedTab = 'users-tab';
    public $wallettopup;
    public bool $showmodal = false;
    public bool $showlinkmodal = false;
    public $banktransactions;
    public $search;
    public function boot(iwallettopupInterface $wallettoprepo,ibanktransactionInterface $banktransactionrepo)
    {
       $this->wallettoprepo = $wallettoprepo;
       $this->banktransactionrepo = $banktransactionrepo;
    }
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Wallet topup request']
        ];
        $this->year = date('Y');
        $this->status = 'PENDING';
        $this->wallettopup = null;
        $this->banktransactions = new Collection();
    }

    public function statuslist()
    {
        return [
            ['id' => 'PENDING', 'label' => 'Pending'],
            ['id' => 'NOTLINKED', 'label' => 'Approved & Not linked'],
            ['id' => 'LINKED', 'label' => 'Approved & Linked'],
            ['id' => 'REJECTED', 'label' => 'Rejected'],
        ];
    }

    public function view($id)
    {
        $this->wallettopup = $this->wallettoprepo->getwallettopup($id);
        $this->showmodal = true;
    }
    public function getwallettoprequests()
    {
     $payload = $this->wallettoprepo->getwallettopups($this->year);
     
     // Check if $payload is a query builder or a collection
     $isCollection = $payload instanceof \Illuminate\Database\Eloquent\Collection;
     
     if($this->status == 'REJECTED')
     {
        if($isCollection) {
            $payload = $payload->where('status', $this->status);
        } else {
            $payload = $payload->where('status', $this->status);
        }
     }
     elseif($this->status == 'LINKED')
     {
        if($isCollection) {
            // For collections, we need to filter manually
            $payload = $payload->where('status', 'APPROVED')
                ->filter(function($item) {
                    return $item->banktransaction != null;
                });
        } else {
            // For query builder
            $payload = $payload->where('status', 'APPROVED')
                ->whereHas('banktransaction', function($query) {
                    $query->whereNotNull('banktransaction');
                });
        }
     }
     elseif($this->status == 'NOTLINKED')
     {
        if($isCollection) {
            // For collections, we need to filter manually
            $payload = $payload->where('status',"APPROVED")
                ->filter(function($item) {
                    return $item->banktransaction == null;
                });
        } else {
            // For query builder
            $payload = $payload->where('status', "APPROVED")
                ->whereHas('banktransaction', function($query) {
                    $query->whereNull('banktransaction');
                });
        }
     }
     elseif($this->status == 'PENDING')
     {
        $payload = $payload->where('status', $this->status);
     }
     
     return $payload;
    }

    public function makedecision()
    {
        $this->validate([
            'status' => 'required',
            'reason' => 'required_if:status,REJECTED',
        ]);
        $response = $this->wallettoprepo->makedecision($this->wallettopup->id,['decision'=>$this->status,'rejectedreason'=>$this->reason]);
      
        if($response['status']=="success")
        {
            $this->success($response['message']);
        }
        else
        {
            $this->error($response['message']);
        }
      
    }

    public function link($id){
        $transaction = $this->banktransactions->where("id",$id)->first();
        if($transaction->accountnumber != $this->wallettopup->accountnumber){
            $this->error("Account number does not match");
            return;
        }
        if($transaction->amount != $this->wallettopup->amount){
            $this->error("Amount does not match");
            return;
        }
        if($transaction->currency != $this->wallettopup->currency->name){
            $this->error("Currency does not match");
            return;
        }
        $response = $this->banktransactionrepo->link([
            "sourcereference"=>$transaction->sourcereference,
            "regnumber"=>$this->wallettopup->customer->regnumber,
            "wallettopup_id"=>$this->wallettopup->id
        ]);
        if($response['status']=="ERROR"){
            $this->error($response['message']);
            return;
        }
     
        $this->success($response['message']);
        $this->showlinkmodal = false;
    }
   
   
    public function headers():array{
        return [
            ["key"=>"sourcereference","label"=>"Source Reference"],
            ["key"=>"accountnumber","label"=>"Account Number"],
            ["key"=>"description","label"=>"Description"],
            ["key"=>"transactiondate","label"=>"Transaction Date"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"status","label"=>"Status"]
        ];
    }

    public function UpdatedSearch(){
        $this->searchtransactions();
     }
     public function searchtransactions(){
        if($this->search==""){
        
            return;
        }
        $this->banktransactions = $this->banktransactionrepo->internalsearch($this->search);
    }
    
    public function render()
    {
        return view('livewire.admin.finance.wallettopuprequest',['wallettopups'=>$this->getwallettoprequests(),'statuslist'=>$this->statuslist(),'headers'=>$this->headers()]);
    }
}
