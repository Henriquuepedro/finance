<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'spending',
        'earnings',
        'liquid',
        'economy',
        'percentage_economy',
        'reference_month',
        'reference_year'
    ];
}
