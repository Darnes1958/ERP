<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Job extends Model
{

    public function Main(): HasMany
    {
        return $this->hasMany(Main::class);
    }
    public function Main_arc(): HasMany
    {
        return $this->hasMany(Main_arc::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
