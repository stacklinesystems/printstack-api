<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    protected $fillable = [
        'device_id',
        'printer_id',

        'type',
        'content',

        'status',
        'attempts',
        'idempotency_key',
    ];

    protected $casts = [
        'attempts' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS (optional but useful)
    |--------------------------------------------------------------------------
    */

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /*
    |--------------------------------------------------------------------------
    | IDENTITY / DUPLICATION SAFETY
    |--------------------------------------------------------------------------
    */

    public static function findByIdempotency($key)
    {
        return self::where('idempotency_key', $key)->first();
    }

    public static function createUnique(array $data)
    {
        return self::firstOrCreate(
            ['idempotency_key' => $data['idempotency_key'] ?? uniqid('job_')],
            $data
        );
    }
}
