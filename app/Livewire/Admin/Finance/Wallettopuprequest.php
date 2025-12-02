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
    public $status; // This is for the filter dropdown
    public $decisionStatus; // Add this for the modal form
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
        $this->decisionStatus = null; // Add this
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
        $this->decisionStatus = null; // Reset when opening modal
        $this->reason = null; // Reset reason
        $this->showmodal = true;
    }
    public function getwallettoprequests()
    {
        $payload = $this->wallettoprepo->getwallettopups($this->year);
        
        if($this->status === 'REJECTED')
        {
            $payload = $payload->where('status', $this->status);
        }elseif($this->status === 'LINKED')
        {
            // For collections, we need to filter manually
            $payload = $payload->where('status', 'APPROVED')
                ->filter(function($item) {
                    return $item->banktransaction != null;
                });
            
        }elseif($this->status === 'NOTLINKED')
        {
            // For collections, we need to filter manually
            $payload = $payload->where('status',"APPROVED")
                ->filter(function($item) {
                    return $item->banktransaction == null;
            });  
        }
        elseif($this->status === 'PENDING')
        {
            $payload = $payload->where('status', $this->status);
        }else{
            $payload = collect([]); 
        }
        return $payload;
    }

    public function makedecision()
    {
        $this->validate([
            'decisionStatus' => 'required', // Change from 'status' to 'decisionStatus'
            'reason' => 'required_if:decisionStatus,REJECTED', // Change from 'status' to 'decisionStatus'
        ]);
        $response = $this->wallettoprepo->makedecision($this->wallettopup->id,['decision'=>$this->decisionStatus,'rejectedreason'=>$this->reason]);
      
        if($response['status']=="success")
        {
            $this->success($response['message']);
            $this->showmodal = false; // Close modal after success
            $this->decisionStatus = null; // Reset
            $this->reason = null; // Reset
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
        return view('livewire.admin.finance.wallettopuprequest',[
            'wallettopups'=>$this->getwallettoprequests(),
            'statuslist'=>$this->statuslist(),
            'headers'=>$this->headers()
        ]);
    }
}
