<?php

namespace App\Models;

use App\Enums\Haf_kst_type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class HafithaTran extends Model
{
    protected $casts=['haf_kst_type'=>Haf_kst_type::class];
    public function Hafitha(): BelongsTo
    {
        return $this->belongsTo(Hafitha::class);
    }
    public function hafithaable(): MorphTo
    {
        return $this->morphTo();
    }
    public function User(){
        return $this->belongsTo(User::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
