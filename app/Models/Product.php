<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'name',
        'category_id',
        'organization_id',
        'sale_price',
        'cash_price',
        'voucher_price',
        'color',
        'grade',
        'description',
        'quantity',
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
