<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotCommand extends Model
{

    public $table='bot_command';
    public $timestamps = true;
    use HasFactory;
}
