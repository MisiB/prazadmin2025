<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\irevenuepostingInterface;
use App\Interfaces\services\ipalladiumInterface;
use App\Models\Invoice;
use App\Models\Revenuepostingjob;

use App\Models\Revenuepostingjobitem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class _revenuepostingRepository implements irevenuepostingInterface
{
    /**
     * Create a new class instance.
     */
    protected $revenuepostingjob;
    protected $revenuepostingjobitem;
    protected $invoice;
    protected $palladiumservice;
    protected $api;
    public function __construct(Revenuepostingjob $revenuepostingjob, Invoice $invoice,Revenuepostingjobitem $revenuepostingjobitem,ipalladiumInterface $palladiumservice)
    {
        $this->revenuepostingjob = $revenuepostingjob;
        $this->invoice = $invoice;
        $this->revenuepostingjobitem = $revenuepostingjobitem;
        $this->palladiumservice = $palladiumservice;
        $this->api = new Http();
    }
    public function getRevenuePostingJobs($year){
        
        return $this->revenuepostingjob->with('inventoryitem','currency','createdBy','approvedBy')->where('year', $year)->paginate(10);
    }
    public function getRevenuePostingJob($id){
        return $this->revenuepostingjob->with('inventoryitem','currency')->where('id', $id)->first();
    }
    public function getRevenuePostingInvoices($id){
        $revenuepostingjob = $this->revenuepostingjob->with('inventoryitem','currency')->where('id', $id)->first();
        $invoices = $this->invoice->with('inventoryitem','currency')->where('inventoryitem_id', $revenuepostingjob->inventoryitem_id)
                                  ->where('currency_id', $revenuepostingjob->currency_id)
                                  ->where('updated_at', '>=', $revenuepostingjob->start_date)
                                  ->where('updated_at', '<=', $revenuepostingjob->end_date)
                                  ->where('status', 'PAID')
                                  ->get();
        
                                  return["job"=>$revenuepostingjob,"invoices"=>$invoices];
        
    }
    public function createRevenuePostingJob($data){
            try{
                $data['created_by']=Auth::user()->id;
                $revenuepostingjob=  $this->revenuepostingjob->create($data);  
       
       DB::beginTransaction();

       $invoices = DB::table('invoices')
       ->where('inventoryitem_id', $data['inventoryitem_id'])
       ->where('currency_id', $data['currency_id'])
       ->where('updated_at', '>=', $data['start_date'])
       ->where('updated_at', '<=', $data['end_date'])
       ->where('status', 'PAID')
       ->where('Posted', '0')
       ->get(); 
       if(count($invoices)>0){
        $array =[];
        foreach($invoices as $invoice){
            $array[] = [
                'invoice_id'=>$invoice->id,
                'revenuepostingjob_id'=>$revenuepostingjob->id
            ];
        }
          DB::table('revenuepostingjobitems')->insert($array); 
       }   
       DB::commit();
        return ["status"=>"success","message"=>"Revenue Posting Job Created Successfully"];
    }
    catch(Exception $e){
        DB::rollBack();
        return ["status"=>"error","message"=>$e->getMessage()];
    }
    }
    public function updateRevenuePostingJob($id, $data){
        try{
            $data['created_by']=Auth::user()->id;
           $job =$this->revenuepostingjob->where('id', $id)->first();
           $this->revenuepostingjobitem->where('revenuepostingjob_id', $id)->delete();
           $invoices = $this->invoice->with('inventoryitem','currency')->where('inventoryitem_id', $data['inventoryitem_id'])
           ->where('currency_id', $data['currency_id'])
           ->where('updated_at', '>=', $data['start_date'])
           ->where('updated_at', '<=', $data['end_date'])
           ->where('status', 'PAID')
           ->where('Posted', '0')
           ->get(); 
           if(count($invoices)>0){
            $array =[];
            foreach($invoices as $invoice){
                $array[] = [
                    'invoice_id'=>$invoice->id,
                    'revenuepostingjob_id'=>$job->id
                ];
            }
               
            $this->revenuepostingjobitem->insert($array);
           }   
           $job->update($data);
            return ["status"=>"success","message"=>"Revenue Posting Job Updated Successfully"];
        }
        catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteRevenuePostingJob($id){
        try{
           $job= $this->revenuepostingjob->where('id', $id)->first();
         
           if($job->processed == "PENDING"){
            $this->revenuepostingjobitem->where('revenuepostingjob_id', $id)->delete();
            $job->delete();
            return ["status"=>"success","message"=>"Revenue Posting Job Deleted Successfully"];
           }else{
            return ["status"=>"error","message"=>"Revenue Posting Job Cannot Be Deleted"];
           }
        }
        catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function approveRevenuePostingJob($id){
        try{
            $job= $this->revenuepostingjob->where('id', $id)->first();
            if($job->status == "PENDING"){
                $job->status = "APPROVED";
                $job->approved_by = Auth::user()->id;
                $job->save();
                return ["status"=>"success","message"=>"Revenue Posting Job Approved Successfully"];
            }else{
                return ["status"=>"error","message"=>"Revenue Posting Job Cannot Be Approved"];
            }
        }
        catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function processPendingRevenuePostingJobs(){
        try{
            $job= $this->revenuepostingjob->with('revenuepostingjobitems.invoice.currency','revenuepostingjobitems.invoice.customer','revenuepostingjobitems.invoice.inventoryitem','revenuepostingjobitems.invoice.receipts.suspense.banktransaction','revenuepostingjobitems.invoice.receipts.suspense.onlinepayment','revenuepostingjobitems.invoice.receipts.suspense.wallettopup.banktransaction')->where('processed', "PENDING")->where("status","APPROVED")->first();
            if($job != null){
                $countitems = $job->revenuepostingjobitems->count();
                $posteditems = 0;
                if($countitems > 0){
                    foreach($job->revenuepostingjobitems as $item){
                       $currency = $item->invoice->currency->name=="USD"? $item->invoice->currency->name : "ZWG";
                       $inventoryitem = $item->invoice->inventoryitem->type;
                       $partnumber = $item->invoice->inventoryitem->code;
                       $regnumber = $item->invoice->customer->regnumber;
                       $tendertype = $currency == "USD" ?  "CBZUSD" : "CBZZWG";
                       $amount = number_format($item->invoice->amount,2);
                       

                       /*
                       1. if currency is USD postfix with USD
                       2.check if account exists in palladium
                       3. if account exists, post invoice
                       4. if account does not exist, create account and post invoice
                       */
                       if($currency == "USD"){
                        $regnumber = $regnumber."-USD";
                        if($item->invoice->inventoryitem->type == "BIDBOND"){
                            $regnumber = $regnumber."S".$currency;
                        }
                       }
                       $checkcustomer = $this->palladiumservice->retrieve_customer($regnumber);
                       if(Str::contains(Str::lower($checkcustomer),"record not found",true)){
                        $formdata = [
                            "accountname"=>$item->invoice->customer->name,
                            "accountregnumber"=>$regnumber,
                            "currency"=>$currency
                        ];
                        $response =  $item->invoice->inventoryitem->type == "BIDBOND" ? $this->palladiumservice->create_supplier_account($formdata) : $this->palladiumservice->create_customer_account($formdata);
                        if($response['status'] == "error"){
                           $item->comment = $response['message'];
                           $item->save();
                           continue;
                        }
                       }


                         /// get gl account

                       $glaccount = $this->get_gl_account($currency,$inventoryitem);
                       if($glaccount != null){

                        $invoicenumber = $item->invoice->invoicenumber;
                        if($item->invoice->receipts->count() > 0){
                            $receipt = $item->invoice->receipts->last();
                            $invoicedate = $receipt->created_at;
                            $suspense = $receipt->suspense;
                            if($suspense != null){
                                $depositdate = "";
                                if($suspense->source="banktransactions"){
                                    $depositdate = $suspense->banktransaction->created_at;
                                }else if($suspense->source="onlinepayments"){
                                    $depositdate = $suspense->onlinepayment->created_at;
                                }else if($suspense->source="wallettopups"){
                                    if($suspense->wallettopup->banktransaction != null){
                                        $depositdate = $suspense->wallettopup->banktransaction->created_at;
                                    }else{
                                        $item->comment = "Wallet topup not linked to bank transaction";
                                        $item->save();
                                        continue;
                                    }
                                }
                                  $url = "";
                                  $postdata = [];
                                  if($item->invoice->inventoryitem->type!="BIDBOND"){
                                    $url = $this->api."ProcessCustomerInvoice";
                                    $postdata = [  "CustomerId" => $regnumber,
                                    "ReceiptAccoountNumber" => $glaccount,
                                    "ReceiptComment" => $invoicenumber,
                                    "ReceiptTenderType" => $tendertype,
                                    "InvoiceDate" => $invoicedate,
                                    "Amount" => $amount,
                                    "Currency" => $currency,
                                    "PartNumber" => $partnumber,
                                    "reference" => $invoicenumber . "-" . $invoicedate
                                  ];
                                  }else{
                                    $url = $this->api."ProcessSupplierInvoice";
                                    $postdata = [ 
                                        "SupplierId" => $regnumber,
                                        "InvoiceNumber" => $invoicenumber,
                                        "InvoiceDate" => $invoicedate,
                                        "Amount" => $amount,
                                        "Currency" => $currency,
                                        "PartNumber" => $partnumber,
                                        "reference" => $invoicenumber . "-" . $invoicedate
                                  ];
                                  }

                                  $response = Http::asJson()->post($url,$postdata);
                                  $string = $response->body();
                                  if(Str::contains(Str::lower($string),"success",true)){
                                    $item->invoice->posted=1;
                                    $item->invoice->save();
                                     $item->status = "POSTED";
                                     $item->save();
                                    continue;
                                  }else{
                                    $item->comment = "Invoice Posting Failed";
                                    $item->save();
                                    continue;
                                  }

                                
                            }else{
                                $item->comment = "Suspense Not Found";
                                $item->save();
                                continue;
                            }
                        }else{
                           $item->comment = "Receipt Not Found";
                           $item->save();
                           continue;
                        }
                        
                       }else{
                        $item->comment = "GL Account for currency.".$currency." and inventoryitem.".$inventoryitem." Not Found";
                        $item->save();
                        continue;
                       }
                    }
                    $posteditems = $this->revenuepostingjobitem->where('revenuepostingjob_id', $job->id)->where("status","POSTED")->count();
                    if($posteditems == $countitems){
                        $job->processed = "POSTED";
                        $job->save();
                    }
                }
            }
            return ["status"=>"success","message"=>"Revenue Posting Jobs Processed Successfully"];
        }
        catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
    public function deleteRevenuePostingJobItems($id){
        try{
            $job= $this->revenuepostingjobitem->where('id', $id)->first();
            if($job->status == "PENDING"){
                $job->delete();
                return ["status"=>"success","message"=>"Revenue Posting Job Item Deleted Successfully"];
            }else{
                return ["status"=>"error","message"=>"Revenue Posting Job Item Cannot Be Deleted"];
            }
        }
        catch(Exception $e){
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
   public function getRevenuePostingJobItems($id){
    DB::beginTransaction();
    try{
        $jobitems = DB::table('revenuepostingjobitems')
         ->join('invoices','revenuepostingjobitems.invoice_id','invoices.id')
         ->join('customers','invoices.customer_id','customers.id')
         ->join('inventoryitems','invoices.inventoryitem_id','inventoryitems.id')
         ->join('currencies','invoices.currency_id','currencies.id')
         ->select('revenuepostingjobitems.*','invoices.amount','customers.name as customer_name','inventoryitems.name as inventoryitem_name','currencies.name as currency_name','invoices.invoicenumber','invoices.updated_at','invoices.posted')
         ->where('revenuepostingjob_id', $id)->get();
        DB::commit();
        return $jobitems;
    }
    catch(Exception $e){
        DB::rollBack();
        return ["status"=>"error","message"=>$e->getMessage()];
    }
   }

   public function get_gl_account($currency,$inventoryitemtype){
        if ($currency == "USD" && $inventoryitemtype == "NONREFUNDABLE") {
            return "1055-0005";
        } else if ($currency == "USD" && $inventoryitemtype == "REFUNDABLE") {
            return "1055-0010";
        } else if ($currency == "ZWG" && $inventoryitemtype == "NONREFUNDABLE") {
            return "1050-0005";
        } else if ($currency == "ZWG" && $inventoryitemtype == "REFUNDABLE") {
            return "1050-0015";
        } else if ($currency == "ZiG" && $inventoryitemtype == "NONREFUNDABLE") {
            return "1050-0005";
        } else if ($currency == "ZiG" && $inventoryitemtype == "REFUNDABLE") {
            return "1050-0015";
        }
    
    
   }
}
