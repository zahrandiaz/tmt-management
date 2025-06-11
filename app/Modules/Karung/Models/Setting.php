<?php

namespace App\Modules\Karung\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'karung_settings';

    protected $fillable = [
        'business_unit_id',
        'setting_key',
        'setting_value',
    ];
}