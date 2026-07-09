<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCharacteristic extends Model
{
    protected $fillable = ['product_id', 'groupe', 'caracteristique', 'valeur', 'unite', 'order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
