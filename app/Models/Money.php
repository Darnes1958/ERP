<?php

namespace App\Models;

use App\Enums\RecWhoMoney;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Money extends Model
{
    public function Price_type(){
      return $this->belongsTo(Price_type::class);
    }
  public function Kazena(){
    return $this->belongsTo(Kazena::class);
  }
  public function Acc(){
    return $this->belongsTo(Acc::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts =[
    'rec_who' => RecWhoMoney::class,
  ];

}
