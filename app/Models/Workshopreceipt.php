<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workshopreceipt extends Model
{
    use HasFactory;
    // protected $fillable = [
    //     'workshopinvoice_id',
    //     'receipt_number',
    //     'amount',
    //     'payment_method',
    //     'reference_number',
    //     'payment_date',
    //     'status',
    //     'notes',
    //     'document_url',
    //     'created_by'
    // ];

    // public function workshopinvoice()
    // {
    //     return $this->belongsTo(WorkshopInvoice::class, 'workshopinvoice_id', 'id');
    // }
    
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'created_by', 'id');
    // }
}
