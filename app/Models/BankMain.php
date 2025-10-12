<?php

namespace App\Models;

use App\Enums\R_type;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BankMain extends Model
{
    protected $casts=['r_type'=>R_type::class,];
    public function Taj()
    {
        return $this->hasMany(Taj::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
