<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'price',
        'reference_month',
        'reference_year'
    ];
}
