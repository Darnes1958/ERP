<?php

namespace App\Models;

use App\Enums\OverMorph;
use App\Enums\Status;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Overkst extends Model
{
  protected $connection = 'other';
    protected $casts = [
        'status' => Status::class,'overkstable_type'=>OverMorph::class,
    ];
    protected $appends =['name'];
    public function getNameAttribute(){
        return $this->overkstable->name;
    }
  public function overkstable(): MorphTo {
    return $this->morphTo();
  }
  public function tarkst()
    {
        return $this->morphMany(Tarkst::class, 'tarkstable');
    }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection = Auth::user()->company;
    }
  }
}
