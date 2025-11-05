<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;
use App\Interfaces\repositories\ibanktransactionInterface;
use Illuminate\Support\Collection;
use Mary\Traits\Toast;

class Searchtransactions extends Component
{
    use Toast;
    public $search;
    protected $repo;
    public $transactionmodal = false;
    public $transaction = null;
    public $transactions ;
    public $customer;
    public function boot(ibanktransactionInterface $repo)
    {
        $this->repo = $repo;
    }

    public function mount($customer=null)
    {
        $this->customer = $customer;
        $this->transactions = new Collection();
    }
    public function searchtransactions()
    {
        if($this->search)
        {
            $transactions = $this->repo->internalsearch($this->search);
            $this->transactions = $transactions;
            return $transactions;
           
        }
        return new Collection();
    }
    public function headers(): array
    {
        return [
            ['key' => 'Description', 'label' => 'Description']
        ];
    }
    public function blockTransaction($id)
    {
       $response= $this->repo->block($id, "BLOCKED");
       if($response['status']=="SUCCESS"){
        $this->success($response['message']);
       }else{
        $this->error($response['message']);
       }
    }

    public function claimTransaction($id)
    {
        $transaction = $this->transactions->where('id', $id)->first();
       
        $response= $this->repo->claim([
            'sourcereference' => $transaction->sourcereference,
            'regnumber' => $this->customer->regnumber
        ]);
        if($response['status']=="SUCCESS"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }
    public function unblockTransaction($id)
    {
        $response= $this->repo->block($id, "PENDING");
        if($response['status']=="SUCCESS"){
            $this->success($response['message']);
           }else{
            $this->error($response['message']);
           }
    }
    public function viewTransaction($id)
    {
        $response= $this->repo->gettransaction($id);
       
        $this->transactionmodal = true;
        $this->transaction = $response;
    }
    public function render()
    {
        return view('livewire.admin.finance.searchtransactions',[
            'transactions' => $this->searchtransactions(),
            'headers' => $this->headers()
        ]);
    }
}
