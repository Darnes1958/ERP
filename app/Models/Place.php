<?php

namespace App\Models;

use App\Enums\PlaceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Place extends Model
{
  protected $connection = 'other';

  protected $casts =[
    'place_type' => PlaceType::class,
  ];
  public function Place_stock(){
    return $this->hasMany(Place_stock::class);
  }

  public function Buy(){
    return $this->hasMany(Buy::class);
  }
    public function Buys_work(){
        return $this->hasMany(Buys_work::class);
    }

    public function Salary()
    {
        return $this->hasMany(Salary::class);
    }
    public function Rent()
    {
        return $this->hasMany(Rent::class);
    }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
