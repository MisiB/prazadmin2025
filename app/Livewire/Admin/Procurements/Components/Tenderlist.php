<?php

namespace App\Livewire\Admin\Procurements\Components;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\itenderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Tender;
use App\Models\Customer;
use App\Models\Tendertype;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Facades\Excel;


class Tenderlist extends Component
{
    use Toast, WithFileUploads;
    public $search;
    protected $tenderrepo;
    protected $inventoryitemrepo;
    protected $currencyrepo;
    public $importmodal = false;
    public $file;
    public $tender=null;
    public $modal = false;
    public $edittendermodal = false;
    public $tender_number;
    public $tender_title;
    public $status;
    public $tender_type;
    public $closing_date;
    public $closing_time;
    public $tender_description;
    public $addtenderfeemodal = false;
    public $inventoryitem_id;
    public $currency_id;
    public $amount;
    public $validityperiod;
    public $tender_id;
    public $tenderfee_id;


    public function boot(itenderInterface $tenderrepo,iinventoryitemInterface $inventoryitemrepo,icurrencyInterface $currencyrepo)
    {
        $this->tenderrepo = $tenderrepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->currencyrepo = $currencyrepo;
    }

      // Open import modal
      public function openImportModal()
      {
          $this->reset(['file']); // Reset any previous file
          $this->importmodal = true;
      }
  
      // Close import modal
      public function closeImportModal()
      {
          $this->importmodal = false;
          $this->reset('file');
      }

public function importTenders()
{
    $this->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:20480',
    ]);

    try {
        $path = $this->file->store('tenders', 'local');
        $fullPath = storage_path('app/private/' . $path);

        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $fullPath)[0];

        $uploaded = 0;

        foreach ($rows as $i => $row) {

            // Skip header
            if ($i === 0) continue;

            $regnumber          = $row[0] ?? null;
            $tenderid           = $row[1] ?? null;
            $tendernumber      = $row[2] ?? null;
            $tendertitle       = $row[3] ?? null;
            $tenderdescription = $row[4] ?? null;
            $closingdate       = $row[5] ?? null;
            $closingtime       = $row[6] ?? null;
            $status             = $row[7] ?? "PUBLISHED";
            $suppliercategories = $row[8] ?? "[]";
            
            // Parse supplier categories - handle JSON string format
            $parsedCategories = [];
            if (!empty($suppliercategories) && $suppliercategories !== "[]") {
                // If it's already a JSON string, decode it
                if (is_string($suppliercategories)) {
                    $decoded = json_decode($suppliercategories, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $parsedCategories = $decoded;
                    } else {
                        // If not valid JSON, try splitting by comma (e.g., "GM002,GT002,S002")
                        $parsedCategories = array_map('trim', explode(',', trim($suppliercategories, '[]')));
                    }
                } elseif (is_array($suppliercategories)) {
                    $parsedCategories = $suppliercategories;
                }
            }

            $source             = $row[9] ?? null;
            $tendertypeid      = $row[10] ?? null;
            $tenderurl         = $row[11] ?? null;
            $tenderfile        = $row[12] ?? null;

            if (!$tendernumber) continue;

            // Skip duplicates
            if (Tender::where('tender_number', $tendernumber)->exists()) {
                continue;
            }

            //REGNUMBER ---
            $customer = Customer::where("regnumber", $regnumber)->first();
            if (!$customer) continue;
            $customer_id = $customer->id;

            // Parse closing date - handle Excel serial numbers and various formats
            $parsedDate = null;
            if (!empty($closingdate)) {
                try {
                    // Check if it's an Excel serial number (numeric value)
                    if (is_numeric($closingdate)) {
                        // Excel serial number: days since 1900-01-01
                        // Excel incorrectly treats 1900 as a leap year, so we subtract 1 day
                        $excelEpoch = \Carbon\Carbon::create(1899, 12, 30);
                        $parsedDate = $excelEpoch->copy()->addDays((int)$closingdate);
                    } else {
                        // Try parsing as a date string
                        $parsedDate = \Carbon\Carbon::parse($closingdate);
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse closing date: {$closingdate} - {$e->getMessage()}");
                    $parsedDate = null;
                }
            }

            // Parse closing time - handle Excel serial numbers and various formats
            $parsedTime = null;
            if (!empty($closingtime)) {
                try {
                    // Check if it's an Excel serial number (numeric value between 0 and 1)
                    if (is_numeric($closingtime) && $closingtime < 1) {
                        // Excel time is a fraction of a day
                        $hours = (int)($closingtime * 24);
                        $minutes = (int)(($closingtime * 24 - $hours) * 60);
                        $parsedTime = sprintf('%02d:%02d', $hours, $minutes);
                    } elseif (is_numeric($closingtime) && $closingtime >= 1) {
                        // Might be a full datetime serial number, extract time portion
                        $decimalPart = $closingtime - floor($closingtime);
                        $hours = (int)($decimalPart * 24);
                        $minutes = (int)(($decimalPart * 24 - $hours) * 60);
                        $parsedTime = sprintf('%02d:%02d', $hours, $minutes);
                    } else {
                        // Try parsing as a time string
                        $parsedTime = \Carbon\Carbon::parse($closingtime)->format('H:i');
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse closing time: {$closingtime} - {$e->getMessage()}");
                    $parsedTime = null;
                }
            }

            // Skip if required dates are missing (since columns are NOT NULL)
            if (!$parsedDate) {
                \Log::warning("Skipping tender {$tendernumber}: missing or invalid closing date");
                continue;
            }

            // Insert tender
            Tender::create([
                'customer_id'        => $customer_id,
                'tender_id'          => $tenderid,
                'tender_number'      => $tendernumber,
                'tender_title'       => $tendertitle,
                'tender_description' => $tenderdescription,
                'closing_date'       => $parsedDate->format('Y-m-d'),
                'closing_time'       => $parsedTime ?? '00:00:00', // Default to midnight if not provided
                'status'             => $status,
                'suppliercategories' => $parsedCategories, // Use parsed array instead of raw string
                'source'             => $source,
                'tendertype_id'      => $tendertypeid,
                'tender_url'         => $tenderurl,
                'tender_file'        => $tenderfile,
            ]);

            $uploaded++;
        }

        $this->success("$uploaded tenders imported successfully.");
        $this->closeImportModal();

    } catch (\Exception $e) {
        \Log::error("Tender Import Error: " . $e->getMessage());
        $this->error("Error importing tenders: " . $e->getMessage());
    }
}




    public function gettenders():LengthAwarePaginator
    {
        return $this->tenderrepo->gettenders($this->search);
    }

    public function getinventoryitems()
    {
        return $this->inventoryitemrepo->getinventories();
    }
    public function getcurrencies()
    {
        return $this->currencyrepo->getcurrencies();
    }

    public function headers():array{
        return [
            ['key'=>'tender','label'=>'Tender'],
            ['key'=>'dates','label'=>'Dates'],
            ['key'=>'tender_type','label'=>'Tender Type'],
            ['key'=>'supplier_categories','label'=>'Supplier Categories'],
            ['key'=>'status','label'=>'Status'],
            ['key'=>'action','label'=>''],
        ];
    }

    public function statuslist():array{
        return [
            ["id"=>"PUBLISHED","name"=>"Published"],
            ["id"=>"CANCELLED","name"=>"Cancelled"]
        ];
    }
    public function gettendertypes()
    {
        return $this->tenderrepo->gettendertypes();
    }

    public function gettender($id){
        $this->tender_id = $id;
        $this->tender= $this->tenderrepo->gettender($id);   
        $this->modal = true;
    }

    public function edittender(){
        $this->edittendermodal = true;
        $this->tender_number = $this->tender->tender_number;
        $this->tender_title = $this->tender->tender_title;
        $this->status = $this->tender->status;
        $this->tender_type = $this->tender->tendertype_id;
        $this->closing_date = $this->tender->closing_date;
        $this->closing_time = $this->tender->closing_time;
        $this->tender_description = $this->tender->tender_description;
    }

    public function savetender(){
       $response= $this->tenderrepo->updatetender($this->tender->id,["tender_number"=>$this->tender_number,"tender_title"=>$this->tender_title,"status"=>$this->status,"tendertype_id"=>$this->tender_type,"closing_date"=>$this->closing_date,"closing_time"=>$this->closing_time,"tender_description"=>$this->tender_description,"suppliercategories"=>[]]);
       if($response['status']=='success'){

        $this->success($response['message']);
       }else{
        $this->error($response['message']);
       }
       $this->edittendermodal = false;
    }

    public function getfee($id){
        $this->tenderfee_id = $id;
        $fee = $this->tenderrepo->gettenderfee($id);
        $this->tender_id = $fee->tender_id;
        $this->inventoryitem_id = $fee->inventoryitem_id;
        $this->currency_id = $fee->currency_id;
        $this->amount = $fee->amount;
        $this->validityperiod = $fee->validityperiod;
        $this->addtenderfeemodal = true;
    }
    public function savetenderfee(){
     if($this->tenderfee_id){
        $this->updatefee();
     }else{
        $this->createfee();
     }
     $this->reset(['tenderfee_id','inventoryitem_id','currency_id','amount','validityperiod']);
    }

    public function createfee(){
        $response= $this->tenderrepo->createtenderfee([
            "tender_id" => $this->tender_id,
            "inventoryitem_id" => $this->inventoryitem_id,
            "currency_id" => $this->currency_id,
            "amount" => $this->amount,
            "validityperiod" => $this->validityperiod,
        ]);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }

    public function updatefee(){
        $response= $this->tenderrepo->updatetenderfee($this->tenderfee_id,["tender_id"=>$this->tender_id,"inventoryitem_id"=>$this->inventoryitem_id,"currency_id"=>$this->currency_id,"amount"=>$this->amount,"validityperiod"=>$this->validityperiod]);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }
    public function deletefee($id){
        $response= $this->tenderrepo->deletetenderfee($id);
        if($response['status']=='success'){

            $this->success($response['message']);
        }else{
            $this->error($response['message']);
        }
        $this->addtenderfeemodal = false;
    }
    public function render()
    {
        return view('livewire.admin.procurements.components.tenderlist',[
            'tenders'=>$this->gettenders(),
            'headers'=>$this->headers(),
            'statuses'=>$this->statuslist(),
            'tendertypes'=>$this->gettendertypes(),
            'inventoryitems'=>$this->getinventoryitems(),
            'currencies'=>$this->getcurrencies(),
            ]);
    }
}
