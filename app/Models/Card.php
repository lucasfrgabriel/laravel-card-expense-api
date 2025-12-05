<?php

namespace App\Models;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'balance',
        'status',
        'brand',
        'user_id',
    ];

    protected $casts = [
        'status' => CardStatusEnum::class,
        'brand' => CardBrandEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
