<?php

declare(strict_types=1);

namespace App\Modules\Geography\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'country_code',
        'country_name',
        'city',
        'region',
        'currency_code',
        'regulatory_notes',
        'payment_methods',
    ];

    protected function casts(): array
    {
        return [
            'regulatory_notes' => 'array',
            'payment_methods' => 'array',
        ];
    }
}
