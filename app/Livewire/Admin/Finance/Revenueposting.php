<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\irevenuepostingInterface;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Collection;
class Revenueposting extends Component
{
    use Toast;
    public $breadcrumbs;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    protected $revenuepostingrepo;
    public $year;
    public $id;
    public bool $modal = false;
    public bool $showitemModal = false;

    public $start_date;
    public $end_date;
    public $inventoryitem_id;
    public $currency_id;
    public $jobitems;
    public function boot(iinventoryitemInterface $repo,icurrencyInterface $currencyrepo,irevenuepostingInterface $revenuepostingrepo)
    {
        $this->inventoryitemrepo = $repo;
        $this->currencyrepo = $currencyrepo;
        $this->revenuepostingrepo = $revenuepostingrepo;
    }
    public function mount()
    {
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['label' => 'Revenue Posting']
        ];
        $this->year = date('Y');
        $this->jobitems = new collection();
    }
    public function getinventoryitems()
    {
        return $this->inventoryitemrepo->getinventories();
    }
    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies();
    }
    public function getrevenuepostingjobs()
    {
        return $this->revenuepostingrepo->getRevenuePostingJobs($this->year);
    }
    public function edit($id)
    {
        $job= $this->revenuepostingrepo->getRevenuePostingJob($id);
        $this->id=$job->id;
        $this->start_date=$job->start_date;
        $this->end_date=$job->end_date;
        $this->inventoryitem_id=$job->inventoryitem_id;
        $this->currency_id=$job->currency_id;
        $this->modal=true;
    }

    public function approve($id){
       $response = $this->revenuepostingrepo->approveRevenuePostingJob($id);
       if($response["status"]=="success"){
           $this->success($response['message']);
       }else{
           $this->error($response['message']);
       }
       
    }

    public function save(){
        $this->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'inventoryitem_id' => 'required',
            'currency_id' => 'required',
        ]);
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
        $this->reset(['start_date','end_date','inventoryitem_id','currency_id']);
        $this->modal=false;

    }

    public function create(){
       $response = $this->revenuepostingrepo->createRevenuePostingJob([
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'inventoryitem_id' => $this->inventoryitem_id,
            'currency_id' => $this->currency_id,
            'year' => $this->year,
        ]);
        if($response["status"]=="success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }
    public function update(){
       $response = $this->revenuepostingrepo->updateRevenuePostingJob($this->id,[
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'inventoryitem_id' => $this->inventoryitem_id,
            'currency_id' => $this->currency_id,
        ]);
        if($response["status"]=="success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }
    public function delete($id){
       $response = $this->revenuepostingrepo->deleteRevenuePostingJob($id);
        if($response["status"]=="success"){
            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
    }

    public function getrevenuepostinginvoices($id)
    {
        return $this->revenuepostingrepo->getrevenuepostinginvoices($id);
    }
    public function getrevenuepostinginvoiceitems($id)
    {
        return $this->revenuepostingrepo->getrevenuepostinginvoiceitems($id);
    }
    public function headers():array{
        return [
            ['key'=>'year','label'=>'Year'],
            ['key'=>'inventoryitem.name','label'=>'Inventory item'],
            ['key'=>'currency.name','label'=>'Currency'],
            ['key'=>'start_date','label'=>'Start Date'],
            ['key'=>'end_date','label'=>'End Date'],
           
            ['key'=>'status','label'=>'Status'],
            ['key'=>'processed','label'=>'Processed'],            
            ['key'=>'createdBy','label'=>'Created By'],
            ['key'=>'invoice_count','label'=>'Total Invoices'],

            ['key'=>'action','label'=>'']
        ];
    }
    public function getjobitems($id){
         $payload = $this->revenuepostingrepo->getRevenuePostingJobItems($id);
        $this->jobitems = $payload;
        $this->showitemModal = true;
    }

    public function exportcsv(){
        $data = $this->jobitems;
    
        $csvFileName = "revenueposting_".date('Y-m-d').".csv";
        $file = fopen($csvFileName, "w");
      
        $array[] = ['customer_name','inventoryitem_name','invoice_number','settlement_date','amount','status','currency_name'];
        foreach ($data as $key => $item) {
    
            $array[] = [
            'customer_name'=>$item->customer_name,
            'inventoryitem_name'=>$item->inventoryitem_name,
            'invoice_number'=>$item->invoicenumber,
            'settlement_date'=>$item->updated_at,
            'amount'=>$item->amount,
            'status'=>$item->posted,
            'currency_name'=>$item->currency_name
            ];
        }

        foreach ($array as $task) {
            fputcsv($file, $task);
        }

        fclose($file);
        return response()->download(public_path($csvFileName))->deleteFileAfterSend(true);
    }  
    
   
    public function render()
    {
        return view('livewire.admin.finance.revenueposting',[
            'jobs'=>$this->getrevenuepostingjobs(),
            'inventoryitems'=>$this->getinventoryitems(),
            'currencies'=>$this->getcurrencies(),
            'headers'=>$this->headers()

        ]);
    }
}
