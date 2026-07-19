<?php

namespace App\Core\v1\Year\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;

    protected $table = 'years';

    protected $fillable = [
        'year',
    ];

    /**
     * Scope for searching by year value
     */
    public function scopeOfYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Custom attribute: Is Current Year
     */
    public function getIsCurrentYearAttribute()
    {
        return $this->year == now()->year;
    }
}
