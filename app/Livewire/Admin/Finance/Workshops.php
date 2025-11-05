<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;
use App\Interfaces\services\iworkshopService;
use Livewire\WithPagination;
use App\Interfaces\repositories\icurrencyInterface;
use Illuminate\Support\Collection;
use App\Interfaces\services\isuspenseService;
use App\Interfaces\repositories\invoiceInterface;
use Mary\Traits\Toast;
class Workshops extends Component
{
    use WithPagination;
    use Toast;
    public $breadcrumbs = [];
    public $workshop_id;
    public $workshop=null;
    public $invoices;
    public $status;
    public $currency_id;
    public $search;
    public $invoice = null;
    public $suspense = null;
    public $invoice_id;
    public $selectedTab = 'users-tab';
    public bool $viewmodal = false;
    public bool $viewinvoicemodal = false;
    protected $workshopService;
    protected $currencyService;
    protected $suspenseService;
    protected $invoiceRepository;
    public function boot(iworkshopService $workshopService, icurrencyInterface $currencyService, invoiceInterface $invoiceRepository, isuspenseService $suspenseService)
    {
        $this->workshopService = $workshopService;
        $this->currencyService = $currencyService;
        $this->suspenseService = $suspenseService;
        $this->invoiceRepository = $invoiceRepository;
    }
 
    public function getworkshops()
    {
        return $this->workshopService->getallworkshops($this->search);
    }
    public function getcurrencies()
    {
        return $this->currencyService->getcurrencies();
    }

    public function getstatuslist(){
        return [
            ['key' => 'AWAITING', 'label' => 'Awaiting'],
            ['key' => 'PENDING', 'label' => 'Pending'],
            ['key' => 'PAID', 'label' => 'Paid'],
        ];
    }

    public function viewworkshop($id)
    {
        $this->workshop_id = $id;
        $this->getinvoices();
       
          $this->viewmodal = true;
       
    }

    public function viewinvoice($id)
    {
        $this->invoice_id = $id;
        $this->getinvoice();
        $this->viewinvoicemodal = true;
    }
    public function getinvoices()
    {
        if($this->workshop_id){
             $this->invoices = $this->workshopService->getworkshopinvoices($this->workshop_id,$this->status,$this->currency_id);
            
        }
        return new Collection();
    }
    public function getinvoice()
    {
        if($this->invoice_id){
            $this->invoice = $this->workshopService->getworkshopinvoicebyid($this->invoice_id);
            $this->getsuspense();
        }
        return null;
    }

    public function getsuspense()
    {
        if($this->invoice){
            $suspense = $this->suspenseService->getwalletbalance($this->invoice->customer->regnumber,'NONREFUNDABLE',$this->invoice->currency->name);
           
            $this->suspense = $suspense;
        }
        return null;
    }
    public function mount()
    {
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Workshops Invoices']
        ];
        $this->invoices = new Collection();
    }
    public function settleinvoice()
    {
        $response = $this->invoiceRepository->settleworkshopinvoice($this->invoice->invoicenumber);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }
    public function headers():array
    {
        return [
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'created_at', 'label' => 'Created At'],
            ['key' => 'action', 'label' => 'Action']
        ];
    }

    public function headerinvoices():array
    {
        return [
            ['key' => 'organisation', 'label' => 'Organisation'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'surname', 'label' => 'Surname'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'invoicenumber', 'label' => 'Invoice Number'],
            ['key' => 'delegates', 'label' => 'Delegates'],
            ['key' => 'cost', 'label' => 'Cost'],
            ['key' => 'currency.name', 'label' => 'Currency'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Created At'],
            ['key' => 'action', 'label' => 'Action']
        ];
    }

  
    public function render()
    {
        $this->getinvoices();
        $this->getsuspense();
        return view('livewire.admin.finance.workshops',[
                'workshops' => $this->getworkshops(),
                'headers' => $this->headers(),
                'currencies' => $this->getcurrencies(),
                'statuslist' => $this->getstatuslist(),
                'headerinvoices' => $this->headerinvoices()
            ]);
        }
    }
