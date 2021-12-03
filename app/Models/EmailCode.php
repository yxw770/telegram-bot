<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCode extends Model
{
    public $table='email_code';
    public $timestamps = false;
    use HasFactory;
}
