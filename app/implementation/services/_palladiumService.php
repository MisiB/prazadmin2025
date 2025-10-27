<?php

namespace App\implementation\services;

use App\Models\Invoice;
use App\Interfaces\services\ipalladiumInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class _palladiumService implements ipalladiumInterface
{
    /**
     * Create a new class instance.
     */
    protected $api;

    public function __construct()
    {
        $test = config("httpconfig.palladium_mode") == "TEST" ? "palladium_test" : "palladium_live";
        $this->api = config("httpconfig.$test");
    }

    public function retrieve_customer($prnumber)
    {

        $response = Http::get($this->api."GetCustomer?CustomerId=" . $prnumber);
        return $response->body();
    }
    public function create_customer_account(array $item){
        
            $currency = $item['currency'];
            $regnumber= $item['regnumber'];
            $accountname = $item['accountname'];
            $response = $this->retrieve_customer($regnumber);
            if(Str::contains($response, "Record Not Found",true)){
              $email = "suppler@suppler.co.zw";
              try {
                $response =  Http::asJson()->post($this->api . "CreateCustomers", [
                    "Name" => $accountname,
                    "CustomerId" => $regnumber,
                    "Currency" => $currency,
                    "Email" => $email,
                    "Phone" => "000000000",
                    "Address" => "customer address"
                ]);
                $string = $response->body();
                if (Str::contains($string, "SUCCESS")) {
                    return ["status"=>"success","message"=>$regnumber . " was" . $response->body()];
                
                } else {
                    return ["status"=>"error","message"=>$regnumber . " was" . $response->body()];
                   
                }
              } catch (\Exception $e) {
                return ["status"=>"error","message"=>$e->getMessage()];
              }
            }

            
        
    }

    public function create_supplier_account(array $item){
        
        $currency = $item['currency'];
        $regnumber= $item['regnumber'];
        $accountname = $item['accountname'];
        $response = $this->retrieve_customer($regnumber);
        if(Str::contains($response, "Record Not Found",true)){
          $email = "suppler@suppler.co.zw";
          try {
            $response =  Http::asJson()->post($this->api . "CreatSupplier", [
                "Name" => $accountname,
                "CustomerId" => $regnumber,
                "Currency" => $currency,
                "Email" => $email,
                "Phone" => "000000000",
                "Address" => "customer address"
            ]);
            $string = $response->body();
            if (Str::contains($string, "SUCCESS")) {
                return ["status"=>"success","message"=>$regnumber . " was" . $response->body()];
            
            } else {
                return ["status"=>"error","message"=>$regnumber . " was" . $response->body()];
               
            }
          } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
          }
        }

        
    
}

    public function get_gl_account($currency,$inventoryitemtype){
        $glaccounts = config("glaccounts");
        $colletion = collect($glaccounts);
        return $colletion->where("currency",$currency)->where("inventoryitemtype",$inventoryitemtype)->first();
    
     }

     public function post_invoice(Invoice $invoice){
        $account = $invoice->account;
        $message = [];
        if($account != null){
            if($invoice->currency != null){
                if($invoice->currency->name != "ZWL")
                {
                  $partnumber = $invoice->inventoryitem->code;
                  $glaccount = $this->get_gl_account($invoice->currency->name,$invoice->inventoryitem->type);
                  if($glaccount != null){

                  }else{
                    return ["status"=>"error","message"=>"glaccount not found"];
                  }
                }
                else
                {
                    return ["status"=>"error","message"=>"ZWL not found"];
                }

            }else{
                return ["status"=>"error","message"=>"Currency Not Found"];
            }

        }else{
          $message =["status"=>"error","message"=>"Account Not Found"];
        }
        return $message;

        
     }
}
