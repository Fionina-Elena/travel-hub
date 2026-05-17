<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'duration_days',
        'route_points',
        'highlights',
        'included',
        'excluded',
        'is_published',
    ];

    protected $casts = [
        'route_points' => 'array',
        'highlights' => 'array',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tour $tour) {
            if (empty($tour->slug)) {
                $tour->slug = Str::slug($tour->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('sort_order');
    }

    public function dates(): HasMany
    {
        return $this->hasMany(TourDate::class)->where('is_active', true);
    }

    public function allDates(): HasMany
    {
        return $this->hasMany(TourDate::class);
    }
}
