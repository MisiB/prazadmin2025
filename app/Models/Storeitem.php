<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storeitem extends Model
{
    protected $table = 'storeitems';
    protected $fillable = ['itemdetail', 'itemcode', 'itemdescription', 'itemtype', 'itemcategory', 'itemunit', 'itemquantity', 'itemprice', 'itemstatus'];
}