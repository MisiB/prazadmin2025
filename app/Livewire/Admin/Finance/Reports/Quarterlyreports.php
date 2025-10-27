<?php

namespace App\Livewire\Admin\Finance\Reports;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\invoiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Quarterlyreports extends Component
{
    protected $invoicerepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $year;
    public $status;
    public array $currencyitems = [];
    public array $inventoryitems = [];
    public $reportdata = [];
    public $processedData = [];
    public $retrievemodal = false;
    public $chartData = [];
    
    public function boot(invoiceInterface $invoicerepo, iinventoryitemInterface $inventoryitemrepo, icurrencyInterface $currencyrepo)
    {
        $this->invoicerepo = $invoicerepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
        
        // Default to current year
        $this->year = date('Y');
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
    
    public function getquarterlyreport()
    {
        if (!$this->year || !$this->status || empty($this->inventoryitems) || empty($this->currencyitems)) {
            session()->flash('error', 'Please fill all required fields');
            return;
        }
        
        $data = $this->invoicerepo->getquarterlyreport($this->year, $this->status, $this->inventoryitems, $this->currencyitems);
        $this->reportdata = $data;
        
        // Process the quarterly data
        $this->processQuarterlyData();
        
        // Close the modal
        $this->retrievemodal = false;
    }
    
    protected function processQuarterlyData()
    {
        if (empty($this->reportdata)) {
            return;
        }
        
        $result = [];
        $chartData = [
            'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'datasets' => []
        ];
        
        // Get all inventory items
        $allInventoryItems = collect();
        foreach (['firstdata', 'seconddata', 'thirddata', 'fourthdata'] as $quarter) {
            if (isset($this->reportdata[$quarter])) {
                foreach ($this->reportdata[$quarter] as $invoice) {
                    $allInventoryItems->push($invoice->inventoryitem);
                }
            }
        }
        $uniqueInventoryItems = $allInventoryItems->unique('id');
        
        // Get all currencies
        $allCurrencies = collect();
        foreach (['firstdata', 'seconddata', 'thirddata', 'fourthdata'] as $quarter) {
            if (isset($this->reportdata[$quarter])) {
                foreach ($this->reportdata[$quarter] as $invoice) {
                    $allCurrencies->push($invoice->currency);
                }
            }
        }
        $uniqueCurrencies = $allCurrencies->unique('id');
        
        // Process data for each inventory item and currency combination
        foreach ($uniqueInventoryItems as $inventoryItem) {
            $inventoryId = $inventoryItem->id;
            $inventoryName = $inventoryItem->name;
            
            foreach ($uniqueCurrencies as $currency) {
                $currencyId = $currency->id;
                $currencyName = $currency->name;
                
                $quarterlyTotals = [
                    'Q1' => 0,
                    'Q2' => 0,
                    'Q3' => 0,
                    'Q4' => 0
                ];
                
                // Calculate totals for each quarter
                if (isset($this->reportdata['firstdata'])) {
                    $quarterlyTotals['Q1'] = $this->calculateQuarterTotal($this->reportdata['firstdata'], $inventoryId, $currencyId);
                }
                
                if (isset($this->reportdata['seconddata'])) {
                    $quarterlyTotals['Q2'] = $this->calculateQuarterTotal($this->reportdata['seconddata'], $inventoryId, $currencyId);
                }
                
                if (isset($this->reportdata['thirddata'])) {
                    $quarterlyTotals['Q3'] = $this->calculateQuarterTotal($this->reportdata['thirddata'], $inventoryId, $currencyId);
                }
                
                if (isset($this->reportdata['fourthdata'])) {
                    $quarterlyTotals['Q4'] = $this->calculateQuarterTotal($this->reportdata['fourthdata'], $inventoryId, $currencyId);
                }
                
                // Calculate quarter-on-quarter changes
                $qoqChanges = [
                    'Q1_Q2' => [
                        'value' => 0,
                        'percentage' => 0,
                        'trend' => 'neutral'
                    ],
                    'Q2_Q3' => [
                        'value' => 0,
                        'percentage' => 0,
                        'trend' => 'neutral'
                    ],
                    'Q3_Q4' => [
                        'value' => 0,
                        'percentage' => 0,
                        'trend' => 'neutral'
                    ]
                ];
                
                // Q1 to Q2 change
                if ($quarterlyTotals['Q1'] > 0 && $quarterlyTotals['Q2'] > 0) {
                    $qoqChanges['Q1_Q2']['value'] = $quarterlyTotals['Q2'] - $quarterlyTotals['Q1'];
                    $qoqChanges['Q1_Q2']['percentage'] = round(($qoqChanges['Q1_Q2']['value'] / $quarterlyTotals['Q1']) * 100, 2);
                    $qoqChanges['Q1_Q2']['trend'] = $qoqChanges['Q1_Q2']['value'] >= 0 ? 'up' : 'down';
                }
                
                // Q2 to Q3 change
                if ($quarterlyTotals['Q2'] > 0 && $quarterlyTotals['Q3'] > 0) {
                    $qoqChanges['Q2_Q3']['value'] = $quarterlyTotals['Q3'] - $quarterlyTotals['Q2'];
                    $qoqChanges['Q2_Q3']['percentage'] = round(($qoqChanges['Q2_Q3']['value'] / $quarterlyTotals['Q2']) * 100, 2);
                    $qoqChanges['Q2_Q3']['trend'] = $qoqChanges['Q2_Q3']['value'] >= 0 ? 'up' : 'down';
                }
                
                // Q3 to Q4 change
                if ($quarterlyTotals['Q3'] > 0 && $quarterlyTotals['Q4'] > 0) {
                    $qoqChanges['Q3_Q4']['value'] = $quarterlyTotals['Q4'] - $quarterlyTotals['Q3'];
                    $qoqChanges['Q3_Q4']['percentage'] = round(($qoqChanges['Q3_Q4']['value'] / $quarterlyTotals['Q3']) * 100, 2);
                    $qoqChanges['Q3_Q4']['trend'] = $qoqChanges['Q3_Q4']['value'] >= 0 ? 'up' : 'down';
                }
                
                $yearlyTotal = array_sum($quarterlyTotals);
                
                if ($yearlyTotal > 0) {
                    $result[] = [
                        'inventory_id' => $inventoryId,
                        'inventory_name' => $inventoryName,
                        'currency_id' => $currencyId,
                        'currency_name' => $currencyName,
                        'q1_total' => $quarterlyTotals['Q1'],
                        'q2_total' => $quarterlyTotals['Q2'],
                        'q3_total' => $quarterlyTotals['Q3'],
                        'q4_total' => $quarterlyTotals['Q4'],
                        'yearly_total' => $yearlyTotal,
                        'qoq_changes' => $qoqChanges
                    ];
                    
                    // Add to chart data
                    $chartData['datasets'][] = [
                        'label' => "$inventoryName ($currencyName)",
                        'data' => array_values($quarterlyTotals),
                        'borderColor' => $this->getRandomColor(),
                        'backgroundColor' => $this->getRandomColor(0.2)
                    ];
                }
            }
        }
        
        $this->processedData = collect($result);
        $this->chartData = $chartData;
    }
    
    protected function calculateQuarterTotal($data, $inventoryId, $currencyId)
    {
        return $data->filter(function ($invoice) use ($inventoryId, $currencyId) {
            return $invoice->inventoryitem_id == $inventoryId && $invoice->currency_id == $currencyId;
        })->sum('amount');
    }
    
    protected function getRandomColor($opacity = 1)
    {
        $colors = [
            'rgba(255, 99, 132, ' . $opacity . ')',
            'rgba(54, 162, 235, ' . $opacity . ')',
            'rgba(255, 206, 86, ' . $opacity . ')',
            'rgba(75, 192, 192, ' . $opacity . ')',
            'rgba(153, 102, 255, ' . $opacity . ')',
            'rgba(255, 159, 64, ' . $opacity . ')',
            'rgba(199, 199, 199, ' . $opacity . ')',
            'rgba(83, 102, 255, ' . $opacity . ')',
            'rgba(40, 159, 64, ' . $opacity . ')',
            'rgba(210, 199, 199, ' . $opacity . ')'
        ];
        
        return $colors[array_rand($colors)];
    }
    
    public function render()
    {
        return view('livewire.admin.finance.reports.quarterlyreports', [
            'inventoryitemlist' => $this->getInventoryItems(),
            'currencyitemlist' => $this->getCurrencyItems(),
            'statuslist' => $this->statuslist()
        ]);
    }
}