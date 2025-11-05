<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkshopInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\WorkshopInvoicesFactory> */
    use HasFactory;

    // Fillable properties to protect mass assignment
    protected $fillable = [
        'workshop_id',
        'name',
        'surname',
        'email',
        'organisation',
        'invoicenumber',
        'delegates',
        'currency_id',
        'cost',
        'status',
        'account_type',
        'customer_id' // Changed from accountId
    ];

    // Relationship to the Workshop model
    public function workshop()
    {
        return $this->belongsTo(Workshop::class, 'workshop_id', 'id');
    }

    // Relationship to the Currency model
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function workshopRtgs()
    {
        return $this->hasMany(WorkshopRtg::class, 'workshopinvoice_id' , 'id');
    }

    public function receipts()
    {
        return $this->hasOne(WorkshopReceipt::class, 'workshopinvoice_id' , 'id');
    }

    // Relationship to the Customer model (changed from Account)
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // Keep account() method for backward compatibility but point to customer
    public function account()
    {
        return $this->customer();
    }

    public function workshoporder()
    {
        return $this->hasOne(Workshoporder::class, 'invoicenumber', 'invoicenumber');
    }
}
