<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class HafithaTran extends Model
{
    public function Hafitha(): BelongsTo
    {
        return $this->belongsTo(Hafitha::class);
    }
    public function hafithaable(): MorphTo
    {
        return $this->morphTo();
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
