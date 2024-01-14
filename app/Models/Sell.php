<?php

namespace App\Models;

use App\Enums\Jomla;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Thiagoprz\CompositeKey\HasCompositeKey;


class Sell extends Model
{
  use HasCompositeKey;
  protected $connection = 'other';


  protected $primaryKey = ['id', 'id2'];

    protected $casts =[
         'Jomla' => Jomla::class,
        ];

  public function Customer(){
    return $this->belongsTo(Customer::class);
  }
  public function Price_type(){
    return $this->belongsTo(Price_type::class);
  }
  public function Place(){
    return $this->belongsTo(Place::class);
  }

  public function Sell_tran(){
    return $this->hasMany(Sell_tran::class);
  }
  public function Tar_sell(){
    return $this->hasMany(Tar_sell::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
