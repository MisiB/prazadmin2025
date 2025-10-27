<?php

namespace App\Models\selfservicedb;

use Illuminate\Database\Eloquent\Model;

class SlfBanktransaction extends Model
{
    protected $table = 'banktransactions';
    protected $connection = 'selfservicedb';
}
