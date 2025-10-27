<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\iexchangerateInterface;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Exchangerate extends Component
{    
    use Toast;

    public $type;
    public $primarycurrencyid;
    public $secondarycurrencyid;
    public $userid;
    public $value;
    public $id;
    public $modal = false;
    protected $repo;
    public $breadcrumbs;
    public $currencyMap=[];
    
    public function boot(iexchangerateInterface $repo)
    {
        $this->repo = $repo;
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Inventories']
        ];
        $this->userid = Auth::user()->id;
        
        $this->setCurrencyMap();

    }
    public function getexchangerates()
    {
        $response = $this->repo->getexchangerates();
        return $response;
    }
    public function edit($id){
        $response = $this->repo->getexchangerate($id);
        $this->id = $id;
        $this->type = $response->type;
        $this->primarycurrencyid = $response->primarycurrency->id;
        $this->secondarycurrencyid = $response->secondarycurrency->id;
        $this->userid = $response->user->userid;
        $this->value = $response->value;

        $this->modal = true;
    }

    public function save(){
        $this->validate([
            'type' => 'required',
            'primarycurrencyid' => 'required',
            'secondarycurrencyid' => 'required',
            'userid' => 'required',
            'value' => 'required',
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['type', 'primarycurrencyid', 'secondarycurrencyid', 'userid', 'value', 'id']);
    }

    public  function create(){
       $response = $this->repo->createexchangerate([
            'type' => $this->type,
            'primary_currency_id' => $this->primarycurrencyid,
            'secondary_currency_id' => $this->secondarycurrencyid,
            'user_id' => $this->userid,
            'value' => $this->value
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function update(){
        $response = $this->repo->updateexchangerate($this->id, [
            'type' => $this->type,
            'primary_currency_id' => $this->primarycurrencyid,
            'secondary_currency_id' => $this->secondarycurrencyid,
            'user_id' => $this->userid,
            'value' => $this->value
        ]);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function delete($id){
        $response = $this->repo->deleteexchangerate($id);
        if($response['status'] == "success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function headers():array    {
        return [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'primarycurrency.name', 'label' => 'Primary Currency'],
            ['key' => 'secondarycurrency.name', 'label' => 'Secondary Currency'],
            ['key' => 'value', 'label' => 'Value'],
            ['key' => 'user.name', 'label' => 'Creator'],
        ];
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['type', 'primarycurrencyid', 'secondarycurrencyid', 'userid', 'value', 'id']);
    }

    public function setCurrencyMap()
    {  
        $this->currencyMap=[];
        Currency::each(function ($currency) {
            $this->currencyMap[] = [
                'id' => $currency->id,
                'name' => $currency->name,
            ];
        });
    }
    public function render()
    {
        return view('livewire.admin.finance.exchangerate',[
            "rows" => $this->getexchangerates(),
            "headers" => $this->headers()
        ]);
    }
}
