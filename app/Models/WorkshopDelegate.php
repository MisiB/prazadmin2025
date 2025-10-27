<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class WorkshopDelegate extends Model
{
    /** @use HasFactory<\Database\Factories\WorkshopDelegatesFactory> */
    use HasFactory;

    // protected $table = 'workshopdelegates';

    protected $fillable = [
        'workshoporder_id', 
        'workshop_id', 
        'name', 
        'surname',  
        'email',
        'phone',
        'designation',
        'national_id', 
        'title', 
        'gender', 
        'type', 
        'company'
    ];

    // Relationship to WorkshopInvoice
    public function workshopInvoice()
    {
        return $this->belongsTo(WorkshopInvoice::class, 'workshopinvoice_id', 'id');
    }

    public function workshoporder()
    {
        return $this->belongsTo(Workshoporder::class, 'workshoporder_id', 'id');
    }

    // Relationship to Workshop
    public function workshop()
    {
        return $this->belongsTo(Workshop::class, 'workshop_id', 'id');
    }
}
