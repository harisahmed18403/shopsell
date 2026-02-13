<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CexProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'cex_id',
        'name',
        'cash_price',
        'sale_price',
        'voucher_price',
        'grade',
        'image_url',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
