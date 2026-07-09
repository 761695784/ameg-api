<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'name', 'slug', 'brand_id', 'category_id', 'subcategory_id',
        'description', 'price_fcfa', 'availability', 'is_active', 'is_featured',
        'views_count', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_fcfa' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function characteristics(): HasMany
    {
        return $this->hasMany(ProductCharacteristic::class)->orderBy('order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    /**
     * Caractéristiques groupées façon fiche produit :
     * ['Tambour' => [...], 'Puissance' => [...], ...]
     */
    public function getGroupedCharacteristicsAttribute()
    {
        return $this->characteristics->groupBy('groupe');
    }
}
