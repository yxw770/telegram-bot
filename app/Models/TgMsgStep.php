<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgMsgStep extends Model
{
    public $table='tg_msg_step';
    public $timestamps = false;
    use HasFactory;
}
