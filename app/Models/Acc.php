<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Acc extends Model
{
    protected $connection = 'other';

  public function SalaryTran(){
    return $this->hasMany(Salarytran::class);
  }
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
