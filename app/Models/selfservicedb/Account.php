<?php

namespace App\Models\selfservicedb;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $connection = 'selfservicedb';
    protected $table = 'accounts';
}
