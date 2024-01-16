<?php

namespace App\Models;

use App\Enums\IncDec;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Price_type extends Model
{
  protected $connection = 'other';
  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts =[
    'inc_dec' => IncDec::class,
  ];

  public function Buy(){
    return $this->hasMany(Buy::class);
  }
  public function Price_buy(){
    return $this->hasMany(Price_buy::class);
  }
    public function Price_sell(){
        return $this->hasMany(Price_sell::class);
    }
    public function Receipt(){
        return $this->hasMany(Receipt::class);
    }

    public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
