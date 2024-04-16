<?php

namespace App\Models;

use App\Enums\ImpExp;
use App\Enums\RecWho;
use App\Enums\RecWhoView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Recsupp extends Model
{
  protected $connection = 'other';


  public function Buy(){
    return $this->belongsTo(Buy::class);
  }
  public function Supplier() {
    return $this->belongsTo(Supplier::class);
  }
  public function Price_type() {
    return $this->belongsTo(Price_type::class);
  }
    public function Acc(){
        return $this->belongsTo(Acc::class);
    }
    public function Kazena(){
        return $this->belongsTo(Kazena::class);
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
    'rec_who' => RecWhoView::class,
    'imp_exp' => ImpExp::class,
  ];

}
