<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AircraftType extends Model
{
    use HasFactory;

    protected $casts = [
        'engine_count' => 'integer',
    ];

    protected $fillable = [
        'type',
        'manufacturer',
        'description',
        'engine_count',
        'engine_type',
    ];
}
