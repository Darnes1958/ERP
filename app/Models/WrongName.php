<?php

namespace App\Models;

use App\Enums\Haf_kst_type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class WrongName extends Model
{

    public function Taj(){
        return $this->belongsTo(Taj::class);
    }
    public function hafitha(): MorphMany
    {
        return $this->morphMany(HafithaTran::class, 'hafithaable');
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
