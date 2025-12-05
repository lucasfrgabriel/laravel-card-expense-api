<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use hasFactory;

    protected $fillable = [
        'card_id',
        'amount',
        'description',
        'date',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
