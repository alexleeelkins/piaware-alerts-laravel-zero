<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;

    protected $table = 'aircraft';

    protected $fillable = [
        'hex',
        'flight',
        'latitude',
        'longitude',
        'knots',
        'altitude',
        'registration',
        'type',
    ];

    protected $casts = [
        'latitude'  => 'decimal:6',
        'longitude' => 'decimal:6',
        'knots'     => 'decimal:2',
        'altitude'  => 'integer',
    ];
}
