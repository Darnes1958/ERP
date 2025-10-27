<?php

namespace App\Models;

use App\Enums\Morahela;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Hafitha extends Model
{
    protected $connection = 'other';

    protected $casts=['status'=>Morahela::class,];
    public function hafithaTrans(): HasMany
    {
        return $this->hasMany(HafithaTran::class);
    }
    public function Taj(){
        return $this->belongsTo(Taj::class);
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
