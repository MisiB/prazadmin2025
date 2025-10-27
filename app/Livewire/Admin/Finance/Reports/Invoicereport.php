<?php

namespace App\Livewire\Admin\Finance\Reports;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\invoiceInterface;
use Illuminate\Pagination\LengthAwarePaginator; 
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Invoicereport extends Component
{
    use WithPagination;
    protected $invoicerepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $fromdate;
    public $todate;
    public $status;
    public array $currencyitems=[];
    public array $inventoryitems=[];
    public $retrievemodal=false;
    public $summarymodal=false;
    public $summarydata=[];
    public function boot(invoiceInterface $invoicerepo,iinventoryitemInterface $inventoryitemrepo,icurrencyInterface $currencyrepo)
    {
        $this->invoicerepo = $invoicerepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
    }
    public function getInventoryItems()
    {
        $inventoryitems = $this->inventoryitemrepo->getinventories();
        return $inventoryitems;
    }
    public function getCurrencyItems()
    {
        $currencyitems = $this->currencyrepo->getcurrencies();
        return $currencyitems;
    }
    public function getInvoicespaginated():LengthAwarePaginator
    {
        if($this->fromdate && $this->todate && $this->status && $this->inventoryitems && $this->currencyitems){
            $invoices = $this->invoicerepo->getInvoicespaginated($this->fromdate, $this->todate, $this->status, $this->inventoryitems, $this->currencyitems);
        
            return $invoices;
        
        }
        return new LengthAwarePaginator([],0,10);
    }
    public function getallinvoices():Collection
    {
        $invoices = $this->invoicerepo->getInvoices($this->fromdate, $this->todate, $this->status, $this->inventoryitems, $this->currencyitems);
        return $invoices;
    }

    public function statuslist(): array
    {
        return [
            ['id' => 'PAID', 'name' => 'PAID'],
            ['id' => 'PENDING', 'name' => 'PENDING'],
            ['id' => 'CANCELLED', 'name' => 'CANCELLED'],
            ['id' => 'AWAITING', 'name' => 'AWAITING'],
        ];
    }

    
    public function exportdocument()
{
    $invoices = $this->getallinvoices();
    
    // If no invoices, return early
    if ($invoices->isEmpty()) {
        return;
    }
    
    // Create the CSV data
    $headers = [
        "Date" => "Creation Date",
        "SettlementDate" => "Settlement Date",
        "Prnumber" => "Prnumber",
        "Account" => "Account",
        "Status" => "Status",
        "InventoryItem" => "Inventoryitem",
        "Invoicenumber" => "Invoicenumber",
        "Currency" => "Currency",
        "Amount" => "Amount",
        "Source" => "Source"
    ];
    
    $rows = [];
    $rows[] = $headers;
    
    foreach ($invoices as $value) {
        $settlementdate = $value->receipts->count() > 0 ? 
            $value->receipts->last()->created_at->format("Y-m-d") : 
            $value->created_at->format("Y-m-d");
            
        $source = [];
        foreach ($value->receipts as $receipt) {
            if ($receipt->suspense && $receipt->suspense->onlinepayment) {
                $source[] = $receipt->suspense->onlinepayment->poll_url;
            }
            if ($receipt->suspense && $receipt->suspense->banktransaction) {
                $source[] = $receipt->suspense->banktransaction->sourcereference;
            }
        }
        $source = implode(", ", $source);
        
        $rows[] = [
            "Date" => $value->created_at->format("Y-m-d"),
            "SettlementDate" => $settlementdate,
            "Prnumber" => $value->customer?->regnumber,
            "Account" => $value->customer?->name,
            "Status" => $value->status,
            "InventoryItem" => $value->inventoryitem->name,
            "Invoicenumber" => $value->invoicenumber,
            "Currency" => $value->currency->name,
            "Amount" => $value->amount,
            "Source" => $source
        ];
    }
    
    // Generate a unique filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "invoices_{$timestamp}.csv";
    
    // Create a temporary file in storage path
    $tempPath = storage_path('app/public/' . $filename);
    
    // Ensure the directory exists
    if (!file_exists(dirname($tempPath))) {
        mkdir(dirname($tempPath), 0755, true);
    }
    
    // Write to the file
    $file = fopen($tempPath, 'w');
    foreach ($rows as $row) {
        fputcsv($file, $row);
    }
    fclose($file);
    
    // Return the file as a download response
    return response()->download($tempPath, $filename, [
        'Content-Type' => 'text/csv',
    ])->deleteFileAfterSend(true);
}    
public function summaryreport()
{
   $data = $this->getallinvoices();
   $groupbyinventoryitem = $data->groupBy("inventoryitem_id");
   $array = [];
   foreach ($groupbyinventoryitem as $key => $value) {
    $inventoryitem = $value[0]->inventoryitem->name;
    $groupbycurrency = $value->groupBy("currency_id");
    foreach ($groupbycurrency as $key => $value) {
        $currency = $value[0]->currency->name;
        $total = $value->sum("amount");
        $array[] = [
            "inventoryitem" => $inventoryitem,
            "currency" => $currency,
            "total" => $total
        ];
    }
   }
    $this->summarydata = collect($array)->sortBy("currency")->values();
    $this->summarymodal = true;
   //return $array;   
}

    public function headers(): array
    {
        return [
            ["key" => "customer.regnumber", "label" => "Reg number"],
            ["key" => "customer.name", "label" => "Name"],
            ["key" => "inventoryitem.name", "label" => "Item"],
            ["key" => "invoicenumber", "label" => "Invoice number"],
            ["key" => "currency.name", "label" => "Currency"],
            ["key" => "amount", "label" => "Amount"],
            ["key" => "created_at", "label" => "Creation Date"],
            ["key" => "receipts", "label" => "Settlement Date"],
            ["key"=>"posted","label"=>"Posted"],
            ["key" => "status", "label" => "Status"]

        ];
    }
    public function render()
    {
        return view('livewire.admin.finance.reports.invoicereport',[
            'headers'=>$this->headers(),
            'invoices'=>$this->getInvoicespaginated(),
            "inventoryitemlist"=>$this->getInventoryItems(),
            "currencyitemlist"=>$this->getCurrencyItems(),
            "statuslist"=>$this->statuslist()


        ]);
    }
}
