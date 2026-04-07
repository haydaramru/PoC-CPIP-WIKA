<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HarsatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'category_key',
        'year',
        'value',
        'unit',
    ];

    protected $casts = [
        'year'  => 'integer',
        'value' => 'decimal:2',
    ];
}
