<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgGetMsg extends Model
{
    public $table='tg_get_msg';
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['userid', 'update_id','message_id','tg_userid','create_at','send_at','msg','bot_id'];
}
