<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourDate extends Model
{
    use HasFactory;

    protected $fillable = ['tour_id', 'date', 'price', 'available_slots', 'is_active'];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
