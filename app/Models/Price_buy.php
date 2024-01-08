<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Price_buy extends Model
{
  protected $connection = 'other';

  public function Price_type(){
    return $this->belongsTo(Price_type::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
