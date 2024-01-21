<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurCompany extends Model
{
  use HasFactory;
  protected $guarded = [];
  protected $table = 'OurCompany';


  public $timestamps = false;

}
