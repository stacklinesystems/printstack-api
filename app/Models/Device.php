<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_online',
        'last_seen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function printers()
    {
        return $this->hasMany(Printer::class);
    }
}