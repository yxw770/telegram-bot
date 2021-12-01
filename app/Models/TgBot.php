<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgBot extends Model
{
    public $table='tg_bot';
    public $timestamps = false;
    use HasFactory;
}
