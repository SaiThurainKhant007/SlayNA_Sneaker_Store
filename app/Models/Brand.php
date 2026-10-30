<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Guarded(['id'])]
class Brand extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'show_in_marquee' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Brands whose logo should scroll in the home page logo strip. */
    public function scopeInMarquee(Builder $query): Builder
    {
        return $query->whereNotNull('logo_path')->where('show_in_marquee', true);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }
}
