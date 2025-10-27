<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ionlinepaymentInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\services\ipaynowInterface;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Onlinepayments extends Component
{
    use WithPagination,Toast;
    public $customer_id;
    public $search;
    public $breadcrumbs=[];
    protected $repo;
    protected $suspenserepo;
    protected $bankaccountrepo;
    protected $paynowservice;
    public function boot(ionlinepaymentInterface $repo,isuspenseInterface $suspenserepo,ipaynowInterface $paynowservice,ibankaccountInterface $bankaccountrepo)
    {
        $this->repo = $repo;
        $this->suspenserepo = $suspenserepo;
        $this->paynowservice = $paynowservice;
        $this->bankaccountrepo = $bankaccountrepo;
    }
    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->breadcrumbs = [
            ["link" => route("admin.customers.showlist"),"label"=>"Customers"],
            ["link" => route("admin.customers.show", $this->customer_id),"label"=>"Customer"],
            ["label"=>"Online Payments"],
        ];
    }

    public function getonlinepayments(){
        return $this->repo->getpayments($this->customer_id);
    }

    public function checkpaymentstatus($id){
        $onlinepayment = $this->repo->getpayment($id);
        if($onlinepayment == null){
            $this->error("Online payment not found");
            return;
        }
        if($onlinepayment->status=="PAID"){
            $this->error("Online payment already verified");
            return;
        }
        if($onlinepayment->invoice == null){
            $this->error("Invoice not found");
            return;
        }
        $bankaccount = $this->bankaccountrepo->getbankaccountbytype($onlinepayment->customer_id,$onlinepayment->invoice->inventoryitem->type);
        if($bankaccount == null){
            $this->error("Bank account not found");
            return;
        }
        $status = $this->paynowservice->checkpaymentstatus([
            "type"=>$bankaccount->account_type,
            "currency_id"=>$bankaccount->currency_id,
            "pollurl"=>$onlinepayment->pollurl,
            "returnurl"=>$onlinepayment->returnurl,
            ]);
        if($status['status']=="PAID"){
           $response = $this->repo->update([
                "id"=>$onlinepayment->id,
                "status"=>$status['status'],
                "method"=>"PAYNOW"
            ]);
            if($response['status']=="success"){
                     $response2 = $this->suspenserepo->create([
                    "customer_id"=>$onlinepayment->customer_id,
                    "sourcetype"=>"onlinepayment",
                    "source_id"=>$onlinepayment->id,
                    "amount"=>$onlinepayment->amount,
                    "currency"=>$onlinepayment->currency->name,
                    "accountnumber"=>$bankaccount->accountnumber,
                    "type"=>$bankaccount->account_type,
                    "status"=>"PENDING",
                    "method"=>"PAYNOW"
                ]);
                if($response2['status']=="success"){
                    $this->success("Online payment verified successfully");
                }else{
                    $this->error("Online payment verification failed");
                }
            }else{
                $this->error("Online payment verification failed");
            }
        }else{
            $this->error($status['message']);
        }
    }

    public function headers():array{
        return [
            ["key"=>"id", "label"=>"ID"],
            ["key"=>"invoice.invoicenumber", "label"=>"Invoice Number"],
            ["key"=>"invoice.inventoryitem.name", "label"=>"Item"],
            ["key"=>"amount", "label"=>"Amount"],
            ["key"=>"status", "label"=>"Status"],
            ["key"=>"created_at", "label"=>"Date"],
        ];
    }
    public function render()
    {
        return view('livewire.admin.customers.components.onlinepayments', [
            'onlinepayments' => $this->getonlinepayments(),
            'headers' => $this->headers(),
        ]);
    }
}
