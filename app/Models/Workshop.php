<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
     /** @use HasFactory<\Database\Factories\WorkshopFactory> */
     use HasFactory;

     protected $fillable = [
         'title',
         'target',
         'location', 
         'start_date',
         'end_date',
         'currency_id',
         'limit',
         'cost',
         'status',
         'created_by',
         'document_url',
     ];
 
     // Relationship with Currency model
     public function currency()
     {
         return $this->belongsTo(Currency::class, 'currency_id', 'id');
     }
 
     // Relationship with User model (creator of the workshop)
     public function creator()
     {
         return $this->belongsTo(User::class, 'created_by' , 'id');
     }
 
     public function orders():HasMany
     {
         return $this->hasMany(workshoporder::class,"workshop_id","id");
     }

     public function delegates():HasMany
     {
         return $this->hasMany(WorkshopDelegate::class,"workshop_id","id");
     }

     public function invoices():HasMany
     {
         return $this->hasMany(WorkshopInvoice::class,"workshop_id","id");
     }
}
