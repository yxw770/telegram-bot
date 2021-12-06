<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipGroup extends Model
{
    public $table='vip_group';
    public $timestamps = false;
    protected $casts = [
        'command_list' => 'array',
    ];
//    protected $fillable = ['command_list', 'name','is_del'];
    use HasFactory;


}
