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
    public function boot(ibanktransactionInterface $repo)
    {
        $this->repo = $repo;
    }
    public function searchtransactions()
    {
        if($this->search)
        {
            return $this->repo->internalsearch($this->search);
           
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
