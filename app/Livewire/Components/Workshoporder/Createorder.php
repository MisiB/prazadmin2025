<?php

namespace App\Livewire\Components\Workshoporder;

use App\Interfaces\services\iexchangerateService;
use App\Interfaces\services\icustomerInterface;
use App\Interfaces\services\iworkshopService;
use App\Interfaces\repositories\icurrencyInterface;
use Livewire\Component;
use Illuminate\Support\Collection;
use Mary\Traits\Toast;
class Createorder extends Component
{
    use Toast;
    public $workshop;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $delegates;
    public $currency_id;
    public $amount;
    public $exchangerate_id;
    public $customer_id;
    public $ordernumber;
    public $invoicenumber;
    public $document;
    public $showCreateModal = false;
    public $search;
    public $accounts;
    public $cost;
    public $rates ;
    protected $workshopService;
    protected $exchangerateService;
    protected $customerService;

    protected $currencyService;


    public function boot(iworkshopService $workshopService,iexchangerateService $exchangerateService,icustomerInterface $customerService,icurrencyInterface $currencyService){
        $this->workshopService = $workshopService;
        $this->exchangerateService = $exchangerateService;
        $this->customerService = $customerService;
        $this->currencyService = $currencyService;
    }

    public function mount($workshop)
    {
        $this->workshop = $workshop;
        $this->currency_id = $workshop->currency_id;
        $this->accounts = new Collection();
        $this->delegates = 1;
        $this->rates = new Collection();
       
    }
    public function getcurrencies(){
        return $this->currencyService->getcurrencies()->where('status','ACTIVE');
    }
    
    public function getexchangerates(){
        $rates = $this->exchangerateService->getexchangeratesbyprimarycurrency($this->currency_id);
        $this->rates = $rates;
        $exchangerates = $rates->map(function($item){
            return [
                'id' => $item->id,
                'name' => $item->primarycurrency->name . ' to ' . $item->secondarycurrency->name.' date: '.$item->created_at->format('d/m/Y').' rate: '.$item->value,
            ];
        })->toArray();
       
        return $exchangerates;
    }

    public function searchAccount(){
        $this->accounts = $this->customerService->searchcustomer($this->search);
    }

    public function selectAccount($id){
        $this->customer_id = $id;
    }

    public function totalcost(){
        $exchangerate = $this->rates->firstWhere('id', $this->exchangerate_id);
        if($exchangerate){
            $this->cost = ($this->workshop->Cost*$this->delegates) * $exchangerate->value;
        }else{
            $this->cost = $this->workshop->Cost*$this->delegates;
        }
       
    }
    public function createorder(){
        try{
      $this->validate([
        'customer_id' => 'required',
        'currency_id' => 'required',
        'exchangerate_id' => 'required',
        'delegates' => 'required',
        'name' => 'required',
        'surname' => 'required',
        'email' => 'required',
        'phone' => 'required',
        'cost' => 'required',
   
      ]);
        $data = [
            'customer_id' => $this->customer_id,
            'workshop_id' => $this->workshop->id,
            'currency_id' => $this->currency_id,
            'exchangerate_id' => $this->exchangerate_id,
            'delegates' => $this->delegates,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'cost' => $this->cost,
         
        ];
        $result = $this->workshopService->createorder($data);
        if($result['status'] == 'success'){
            $this->showCreateModal = false;
            $this->dispatch('ordercreated');
            $this->success('message',$result['message']);
        }else{
            $this->error('message',$result['message']);
        }
    }catch(\Exception $e){
        $this->error('message',$e->getMessage());
    }
    }

    public function accountheaders():array{
        return [
            ['key'=>'name','label'=>'Name'],
            ['key'=>'regnumber','label'=>'Reg Number'],
            ['key'=>'action','label'=>'']
        ];
    }

    public function render()
    {
        return view('livewire.components.workshoporder.createorder',[
            'currencies' => $this->getcurrencies(),
            'exchangerates' => $this->getexchangerates(),
            'accountheaders' => $this->accountheaders(),
            'totalcost' => $this->totalcost()
        ]);
    }
}
