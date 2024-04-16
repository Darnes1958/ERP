<?php

namespace App\Models;

use App\Enums\ImpExp;
use App\Enums\RecWhoView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cust_tran extends Model
{
  protected $connection = 'other';
  protected $table = 'cust_trans';
  protected $primaryKey = false;
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
