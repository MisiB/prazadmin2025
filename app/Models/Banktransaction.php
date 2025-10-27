<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banktransaction extends Model
{
 public function customer()
 {
    return $this->belongsTo(Customer::class,"customer_id");
 }
 public function bank()
 {
    return $this->belongsTo(Bank::class,"bank_id");
 }
 public function bankaccount()
 {
    return $this->belongsTo(Bankaccount::class,"accountnumber");
 }
 public function suspense()
 {
    return $this->hasOne(Suspense::class,"source_id","id")->where("sourcetype","=","banktransactions");
 }
}