<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfos extends Model
{
    public $table='user_infos';
    public $timestamps = false;
    use HasFactory;
}
