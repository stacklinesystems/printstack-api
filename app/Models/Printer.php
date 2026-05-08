<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'connection',
        'address',
        'fingerprint',
        'is_default',
        'is_active',
        'is_online'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_online' => 'boolean'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}