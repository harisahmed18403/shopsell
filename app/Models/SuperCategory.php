<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuperCategory extends Model
{
    use HasFactory, BelongsToOrganization;

    public $incrementing = false;

    protected $fillable = ['id', 'name', 'organization_id'];

    public function productLines(): HasMany
    {
        return $this->hasMany(ProductLine::class);
    }
}
