<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'restaurant_name',
        'logo_url',
        'phone',
        'address',
        'samples_seeded',
    ];

    protected function casts(): array
    {
        return [
            'samples_seeded' => 'boolean',
        ];
    }
}
