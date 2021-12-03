<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgSendMsg extends Model
{
    public $table='tg_send_msg';
    public $timestamps = false;
    use HasFactory;
}
