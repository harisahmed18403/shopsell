<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'sale_price',
        'cash_price',
        'voucher_price',
        'color',
        'grade',
        'description',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cexProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CexProduct::class);
    }
}
