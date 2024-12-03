<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Per extends Model
{
    protected $connection = 'other';

    public function Placefrom()
    {
        return $this->belongsTo(Place::class,'place_from');
    }
    public function Placeto()
    {
        return $this->belongsTo(Place::class,'place_to');
    }

    public function Per_tran(){
        return $this->hasMany(PerTran::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
