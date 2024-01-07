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
    'inc_dec' => PlaceType::class,
  ];

  public function Buy(){
    return $this->hasMany(Buy::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
