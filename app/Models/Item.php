<?php

namespace App\Models;

use App\Enums\TwoUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    protected $connection = 'other';



    public function Place_stock(){
      return $this->hasMany(Place_stock::class);
    }
  public function Price_buy(){
    return $this->hasMany(Price_buy::class);
  }
    public function Price_sell(){
        return $this->hasMany(Price_sell::class);
    }
    public function Company(){
        return $this->belongsTo(Company::class);
    }
    public function Item_type(){
        return $this->belongsTo(Item_type::class);
    }
    public function S_quant(){
        return $this->belongsTo(S_quant::class);
    }
    public function Unita(){
        return $this->belongsTo(Unita::class);
    }
    public function Unitb(){
        return $this->belongsTo(Unitb::class);
    }

    public function Barcode(){
        return $this->hasMany(Barcode::class);
    }
  public function Buy_tran(){
    return $this->hasMany(Buy_tran::class);
  }
  public function Buy_tran_work(){
    return $this->hasMany(Buy_tran_work::class);
  }
  public function Tar_Buy(){
  return $this->hasMany(Tar_buy::class);
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
        'two_unit' => TwoUnit::class,
    ];
}
