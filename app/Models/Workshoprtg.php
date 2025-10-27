<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workshoprtg extends Model
{
    use HasFactory;

    // // Define fillable fields for mass assignment
    // protected $fillable = [
    //     'workshopinvoice_id', 
    //     'bank', 
    //     'accountname', 
    //     'description', 
    //     'referencenumber', 
    //     'status', 
    //     'filename'
    // ];

    // /**
    //  * Get the workshop invoice associated with this workshop RTG.
    //  */
    // public function workshopInvoice()
    // {
    //     return $this->belongsTo(WorkshopInvoice::class, 'workshopinvoice_id', 'id');
    // }
}
