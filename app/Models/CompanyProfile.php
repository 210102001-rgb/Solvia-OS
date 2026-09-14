<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'email',
        'phone',
        'address',
        'website',
        'tax_number',
        'currency',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
