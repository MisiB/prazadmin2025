<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\invoiceInterface;
use Carbon\Carbon;
use Livewire\Component;

class Comparisonreports extends Component
{
    protected $invoicerepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $fromdate;
    public $todate;
    public $fromdate2;
    public $todate2;
    public $status;
    public $rangeperiod;
    public array $currencyitems=[];
    public array $inventoryitems=[];
    public $retrievemodal=false;
    public $rangedata=[];
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
    public function statuslist(): array
    {
        return [
            ['id' => 'PAID', 'name' => 'PAID'],
            ['id' => 'PENDING', 'name' => 'PENDING'],
            ['id' => 'CANCELLED', 'name' => 'CANCELLED'],
            ['id' => 'AWAITING', 'name' => 'AWAITING'],
        ];
    }
    public function rangeperiodlist(): array
    {
        return [
            ['id' => 'weekly', 'name' => 'Weekly'],
            ['id' => 'monthly', 'name' => 'Monthly'],
            ['id' => 'yearly', 'name' => 'Yearly'],
        ];
    }
    public function getrangedata(){
        if (!$this->fromdate || !$this->todate || !$this->status || empty($this->inventoryitems) || empty($this->currencyitems) || !$this->rangeperiod) {
            session()->flash('error', 'Please fill all required fields');
            return;
        }
        
        $days = 7;
        if ($this->rangeperiod == "weekly") {
            $days = 7;
        } elseif ($this->rangeperiod == "monthly") {
            $days = 30;
        } elseif ($this->rangeperiod == "yearly") {
            $days = 365;
        }
        
        $this->fromdate2 = Carbon::parse($this->fromdate)->subDays($days);
        $this->todate2 = Carbon::parse($this->todate)->subDays($days);
        
        $data = $this->invoicerepo->getcomparisonreport(
            $this->fromdate, 
            $this->todate, 
            $this->fromdate2, 
            $this->todate2, 
            $this->status, 
            $this->inventoryitems, 
            $this->currencyitems
        );
        
        $firstdata = $data['firstdata'];
        $seconddata = $data['seconddata'];
        
        // Process the comparison data
        $result = [];
        
        // Group by inventory item and currency
        $firstByInventory = $firstdata->groupBy('inventoryitem_id');
        $secondByInventory = $seconddata->groupBy('inventoryitem_id');
        
        // Get all inventory items from both periods
        $allInventoryIds = collect(array_merge(
            $firstByInventory->keys()->toArray(), 
            $secondByInventory->keys()->toArray()
        ))->unique();
        
        foreach ($allInventoryIds as $inventoryId) {
            $inventoryName = '';
            
            // Get all currencies for this inventory item from both periods
            $firstCurrencies = isset($firstByInventory[$inventoryId]) ? 
                $firstByInventory[$inventoryId]->groupBy('currency_id') : 
                collect();
                
            $secondCurrencies = isset($secondByInventory[$inventoryId]) ? 
                $secondByInventory[$inventoryId]->groupBy('currency_id') : 
                collect();
                
            $allCurrencyIds = collect(array_merge(
                $firstCurrencies->keys()->toArray(), 
                $secondCurrencies->keys()->toArray()
            ))->unique();
            
            foreach ($allCurrencyIds as $currencyId) {
                // Calculate totals for each period
                $currentTotal = isset($firstCurrencies[$currencyId]) ? 
                    $firstCurrencies[$currencyId]->sum('amount') : 0;
                    
                $previousTotal = isset($secondCurrencies[$currencyId]) ? 
                    $secondCurrencies[$currencyId]->sum('amount') : 0;
                
                // Calculate difference and percentage
                $difference = $currentTotal - $previousTotal;
                $percentageChange = $previousTotal > 0 ? 
                    round(($difference / $previousTotal) * 100, 2) : 
                    ($currentTotal > 0 ? 100 : 0);
                
                // Get names for display
                if (empty($inventoryName) && isset($firstCurrencies[$currencyId])) {
                    $inventoryName = $firstCurrencies[$currencyId]->first()->inventoryitem->name;
                } elseif (empty($inventoryName) && isset($secondCurrencies[$currencyId])) {
                    $inventoryName = $secondCurrencies[$currencyId]->first()->inventoryitem->name;
                }
                
                $currencyName = '';
                if (isset($firstCurrencies[$currencyId])) {
                    $currencyName = $firstCurrencies[$currencyId]->first()->currency->name;
                } elseif (isset($secondCurrencies[$currencyId])) {
                    $currencyName = $secondCurrencies[$currencyId]->first()->currency->name;
                }
                
                // Add to results
                $result[] = [
                    'inventory_id' => $inventoryId,
                    'inventory_name' => $inventoryName,
                    'currency_id' => $currencyId,
                    'currency_name' => $currencyName,
                    'current_total' => $currentTotal,
                    'previous_total' => $previousTotal,
                    'difference' => $difference,
                    'percentage_change' => $percentageChange,
                    'trend' => $difference >= 0 ? 'up' : 'down'
                ];
            }
        }
        
        $this->rangedata = collect($result);
        $this->retrievemodal = false;
    }
    public function render()
    {
        return view('livewire.admin.finance.reports.comparisonreports',[
            "inventoryitemlist"=>$this->getInventoryItems(),
            "currencyitemlist"=>$this->getCurrencyItems(),
            "statuslist"=>$this->statuslist(),
            "rangeperiodlist"=>$this->rangeperiodlist()
        ]);
    }
}
