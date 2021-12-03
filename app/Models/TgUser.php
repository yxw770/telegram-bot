<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgUser extends Model
{
    public $table='tg_user';
    public $timestamps = false;
    use HasFactory;
}
