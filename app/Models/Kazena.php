<?php

namespace App\Models;

use App\Enums\ImpExp;
use App\Enums\RecWhoView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Kazena extends Model
{
    protected $connection = 'other';
    public function Receipt(){
        return $this->hasMany(Receipt::class);
    }

    public function Recsupp(){
        return $this->hasMany(Recsupp::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }

}
