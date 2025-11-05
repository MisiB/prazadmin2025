<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Interfaces\services\iworkshopService;
use App\Interfaces\services\iexchangerateService;
use App\Interfaces\services\icustomerInterface;
use App\Interfaces\repositories\icurrencyInterface;
class Editorder extends Component
{
    use Toast;
    public $orderid;
    public $order;
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
    public $workshop;
    public $showCreateModal = false;
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

    public function mount($orderid)
    {
        $this->orderid = $orderid;
        $this->workshop = null;
        $this->order = null;
      
    }

    public function getorder()
    {
        $this->order = $this->workshopService->getorder($this->orderid);
        if($this->order){
            $this->name = $this->order->name;
            $this->surname = $this->order->surname;
            $this->email = $this->order->email;
            $this->phone = $this->order->phone;
            $this->customer_id = $this->order->customer_id;
            $this->delegates = $this->order->delegates;
            $this->currency_id = $this->order->currency_id;
            $this->workshop = $this->order->workshop;
            $this->currency_id = $this->order->currency_id;
            $this->exchangerate_id = $this->order->exchangerate_id;
            $this->cost = $this->order->cost;
        }
        $this->showCreateModal = true;
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

    public function totalcost(){
        $exchangerate = $this->rates->firstWhere('id', $this->exchangerate_id);
        if($exchangerate){
            $this->cost = ($this->workshop->Cost*$this->delegates) * $exchangerate->value;
        }else{
            if($this->workshop){
                $this->cost = $this->workshop->Cost*$this->delegates;
            }
        }
       
    }

    public function saveorder(){
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
            'amount' => $this->cost, 
         
        ];
        $result = $this->workshopService->updateorder($this->orderid,$data);
        if($result['status'] == 'success'){
            $this->dispatch('orderupdated');
            $this->success('message',$result['message']);
        }else{
            $this->error('message',$result['message']);
        }
    }catch(\Exception $e){
        $this->error('message',$e->getMessage());
    }
    }



    public function render()
    {
        return view('livewire.components.workshoporder.editorder',[
            'currencies' => $this->getcurrencies(),
            'exchangerates' => $this->getexchangerates(),
            'totalcost' => $this->totalcost()
        ]);
    }
}
