<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\ipaynowintegrationsInterface;
use App\Models\Bank;
use App\Models\Bankaccount;
use App\Models\Currency;
use Livewire\Component;
use Mary\Traits\Toast;

class Paynowconfig extends Component
{

    use Toast;
    public $key;
    public $token;
    public $type;
    public $bankaccountid;
    public $currencyid;
    public $id;
    public $modal = false;
    protected $repo;
    public $breadcrumbs;
    public $currencyMap=[];
    public $bankMap=[];
    public function boot(ipaynowintegrationsInterface $repo)
    {
        $this->repo = $repo;
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Inventories']
        ];

    }
    public function getpaynowintegrations()
    {
        $response = $this->repo->getpaynowintegrations();
        return $response;
    }
    public  function edit($id){
        $response = $this->repo->getpaynowintegration($id);
        $this->id = $id;
        $this->key = $response->key;
        $this->token = $response->token;
        $this->type = $response->type;
        $this->bankaccountid = $response->bankaccount->id;
        $this->currencyid = $response->currency->id;

        
        Currency::each(function ($currency) {
            $this->currencyMap[] = [
                'id' => $currency->id,
                'name' => $currency->name,
            ];
        });
        Bankaccount::each(function ($account) {
            $this->bankMap[] = [
                'id' => $account->id,
                'name' => $account->account_number,
            ];
        });

        $this->modal = true;
    }

    public function save(){
        $this->validate([
            'key' => 'required',
            'token' => 'required',
            'type' => 'required',
            'currencyid' => 'required',
            'bankaccountid' => 'required',
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['key', 'token', 'type', 'bankaccountid', 'currencyid', 'id']);
    }

    public  function create(){
       $response = $this->repo->createpaynowintegration([
            'key' => $this->key,
            'token' => $this->token,
            'type' => $this->type,
            'bankaccount_id' => $this->bankaccountid,
            'currency_id' => $this->currencyid
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function update(){
        $response = $this->repo->updatepaynowintegration($this->id, [
            'key' => $this->key,
            'token' => $this->token,
            'type' => $this->type,
            'bankaccount_id' => $this->bankaccountid,
            'currency_id' => $this->currencyid
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function delete($id){
        $response = $this->repo->deletepaynowintegration($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public  function headers():array    {
        return [
            ['key' => 'key', 'label' => 'Key'],
            ['key' => 'token', 'label' => 'Token'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'bankaccount.account_number', 'label' => 'Bankaccount'],
            ['key' => 'currency.name', 'label' => 'Currency'],
        ];
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['key', 'token', 'type', 'bankaccountid', 'currencyid', 'id']);
    }


    public function render()
    {
        return view('livewire.admin.finance.paynowconfig',[
            "rows" => $this->getpaynowintegrations(),
            "headers" => $this->headers()
        ]);
    }
}
 