<?php

namespace App\Livewire\Admin\Customers\Components;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\invoiceInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\repositories\itenderInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Invoices extends Component
{
    use Toast;
    public $customer_id;
    protected $invoiceRepository;
    public $invoices;
    public $breadcrumbs =[];
    public $modal = false;
    public $ratemodal = false;
    public $search = '';
    public $rates;
    public $tenders;
    public $ratevalue;
    public $ratelabel;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    protected $exchangraterepo;
    protected $tenderrepo;
    protected $customerrepo;
    protected $suspenserepo;
    public $tendermodal = false;
    public $workshopmodal = false;
    public $tendernumber;
    public $prefix="USD";
    public $invoice_id;

    public  $inventoryitem;
    public  $invoicenumber;
    public  $amount;
    public  $customer;
    public  $currency;
    public  $invoicdate;
    public  $exchangerate;
    public  $convertedamount;

    

    public function boot(invoiceInterface $invoiceRepository,icustomerInterface $customerrepo,iinventoryitemInterface $inventoryitemrepo,icurrencyInterface $currencyrepo,iexchangerateInterface $exchangraterepo,itenderInterface $tenderrepo,isuspenseInterface $suspenserepo)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->customerrepo = $customerrepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
        $this->exchangraterepo = $exchangraterepo;
        $this->tenderrepo = $tenderrepo;
        $this->suspenserepo = $suspenserepo;
    }
    public function mount($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->invoices = new Collection();
        $this->rates =  new collection();
        $this->tenders = new Collection();
        $this->customer = $this->customerrepo->getCustomerById($this->customer_id);
        $this->breadcrumbs = [
            ["label" => "Customers", "link" => route("admin.customers.showlist")],
            ["label" => "customer", "link" => route("admin.customers.show", $this->customer_id)],
            ["label" => "Invoices"],
        ];
    }
    public function showInvoices()
    {
        $this->invoices = $this->invoiceRepository->getInvoicebyCustomer($this->customer_id);
        $invoices = $this->invoices;
        if($this->search){
            $this->invoices = $invoices->filter(function ($invoice) {
                return str_contains(strtolower($invoice->invoicenumber), strtolower($this->search)) ||
                       str_contains(strtolower($invoice->inventoryitem->name), strtolower($this->search));
            });
        }
       
    }
    public function searchtender(){
        if($this->tendernumber){
            $this->tenders = $this->tenderrepo->gettendersbynumber($this->tendernumber);
        }
    }
    /**
     * Set the tender fee details and update converted amount
     */
    public function setTender($tender_id, $tenderfee_id)
    {
        $tender = $this->tenders->where("id",$tender_id)->first();
        $tenderfee = $tender->tenderfees->where("id",$tenderfee_id)->first();
        $this->currency = $tenderfee->currency_id;
        $this->amount = $tenderfee->amount;
        $this->tendermodal = false;
        
        // Update the converted amount with the new amount
        $this->updateConvertedAmount();
        
        $this->success("Tender fee set successfully");
    }
    public function headers():array{
        return [
            ["key"=>"created_at","label"=>"Date"],
            ["key"=>"invoicesource","label"=>"Source"],
            ["key"=>"invoicenumber","label"=>"Invoice Number"],
            ["key"=>"inventoryitem.name","label"=>"Item"],
            ["key"=>"amount","label"=>"Amount"],
            ["key"=>"status","label"=>"Status"],

        ];
    }
    public function getExchangeRate()
    {
        if($this->currency==null){
            $this->error("Currency is required");
            return;
        }
        
            $rates = $this->exchangraterepo->getexchangeratesbyprimarycurrency($this->currency);
       
            $this->rates = $rates;
            $this->ratemodal = true;
        
    }

    /**
     * Listen for changes to the inventoryitem property
     */
    public function updatedInventoryitem()
    {
        $this->setInventoryItem();
    }
    /**
     * Update converted amount when amount changes
     */
    public function updatedAmount()
    {
        $this->updateConvertedAmount();
    }
    
    /**
     * Update converted amount when rate value changes
     */
    public function updatedRatevalue()
    {
        $this->updateConvertedAmount();
    }
    
    /**
     * Calculate and update the converted amount
     */
    protected function updateConvertedAmount()
    {
        $amount = $this->amount ?? 0;
        $rate = $this->ratevalue ?? 1;
        $this->convertedamount = $amount * $rate;
    }


    /**
     * Handle inventory item selection and show appropriate modals
     */
    public function setInventoryItem()
    {
        if($this->inventoryitem){
            $item = $this->inventoryitemrepo->getinventory($this->inventoryitem);
            if($item){
                if(in_array($item->name,['ESTABLISHMENT','BIDBOND','SPOC','CONTRACT'])){
                    // Show tender modal
                    $this->tendermodal = true;
                }elseif(in_array($item->name,['WORKSHOP','EXHIBITOR','CONFERENCE'])){
                    // Show workshop modal
                    $this->workshopmodal = true;
                }
            }
        }
    }
    

    /**
     * Set the exchange rate and update converted amount
     */
    public function setExchangeRate($id)
    {
        $rate = $this->rates->where("id",$id)->first();
        $this->ratevalue = $rate->value;
        $this->exchangerate = $rate->id;
        $this->ratelabel = $rate->primarycurrency->name."1  = ". $rate->secondarycurrency->name." ". $rate->value;
        $this->ratemodal = false;
        $this->prefix = $rate->secondarycurrency->name;
        // Update the converted amount with the new rate
        $this->updateConvertedAmount();
    }

    public function save(){
        $this->validate([
            "amount"=>"required",
            "currency"=>"required",
            "invoicdate"=>"required",
            "exchangerate"=>"required",
            "convertedamount"=>"required",
        ]);
        if($this->invoice_id){
            $this->updateinvoice();
        }else{
            $this->createinvoice();
        }
    }

    public function createinvoice(){
        $inventoryitem = $this->inventoryitemrepo->getinventory($this->inventoryitem);
        $currency = $this->currencyrepo->getcurrency($this->currency);
        $response = $this->invoiceRepository->createinvoice([
            "regnumber"=>$this->customer->regnumber,
            "itemcode"=>$inventoryitem->name,
            "invoicenumber"=>$this->invoicenumber,
            "amount"=>$this->amount,
            "description"=>$inventoryitem->name,
            "currency"=>$currency->name,
            "invoicedate"=>$this->invoicdate,
            "exchangerate_id"=>$this->exchangerate,
            "invoicesource"=>"MANUAL",
            "amount"=>$this->convertedamount,
        ]);
        if(strtolower($response['status'])=='success'){
            $this->success($response['message']);
            $this->modal = false;
        }else{
            $this->error($response['message']);
        }
    }

    public function updateinvoice(){
        $inventoryitem = $this->inventoryitemrepo->getinventory($this->inventoryitem);
        $currency = $this->currencyrepo->getcurrency($this->currency);
        $response = $this->invoiceRepository->updateinvoice($this->invoice_id,[
            "customer_id"=>$this->customer_id,
            "inventoryitem_id"=>$this->inventoryitem,
            "invoicenumber"=>$this->invoicenumber,
            "amount"=>$this->amount,
            "description"=>$inventoryitem->name,
            "currency"=>$currency->name,
            "invoicedate"=>$this->invoicdate,
            "exchangerate_id"=>$this->exchangerate,
            "invoicesource"=>"MANUAL",
            "amount"=>$this->convertedamount,
        ]);
        if($response['status']=='success'){
            $this->success($response['message']);
            $this->modal = false;
        }else{
            $this->error($response['message']);
        }
    }

    public function settle($invoice_id){
        $invoice = $this->invoiceRepository->getInvoiceDetails($invoice_id);
        $regnumber = $invoice->customer->regnumber;
        $accounttype = $invoice->inventoryitem->type;
        $currency = $invoice->currency->name;
        $amount = $invoice->amount;
        $walletbalance = $this->suspenserepo->getwalletbalance($regnumber,$accounttype,$currency);
        if($walletbalance<$amount){
            $this->error("Insufficient wallet balance of ".$currency." ".$walletbalance." for ".$accounttype);
            return;
        }
        $response = $this->invoiceRepository->settleInvoice($invoice->invoicenumber,null);
        if($response['status']=='success'){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
   
        
    }
    
    public function render()
    {
        
        return view('livewire.admin.customers.components.invoices',[
            'invoices'=>$this->showInvoices(),
            'headers'=>$this->headers(),
            "inventoryitems"=>$this->inventoryitemrepo->getinventories(),
            "currencies"=>$this->currencyrepo->getcurrencies(),
            "exchangerates"=>$this->rates,
          
           
        ]);
    }
}
