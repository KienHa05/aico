<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'provider',
    'provider_id',
    'provider_email',
])]
class SocialAccount extends Model
{
    use HasFactory;

    /**
     * Get the user that owns this social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
