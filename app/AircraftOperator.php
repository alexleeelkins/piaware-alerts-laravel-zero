<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AircraftOperator extends Model
{
    use HasFactory;

    protected $fillable = [
        'icao',
        'iata',
        'callsign',
        'name',
        'country',
        'location',
        'phone',
        'shortname',
        'url',
        'wiki_url',
    ];
}
