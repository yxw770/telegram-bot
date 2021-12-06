<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgMsgStep extends Model
{
    public $table = 'tg_msg_step';
    public $timestamps = false;
    use HasFactory;

    public function getParamsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = json_encode($value);
    }
}
