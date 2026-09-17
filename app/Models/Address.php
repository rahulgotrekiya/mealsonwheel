<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['user_id', 'street', 'city', 'state', 'zip_code'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSingleLineAttribute(): string
    {
        return "{$this->street}, {$this->city}, {$this->state} {$this->zip_code}";
    }
}
