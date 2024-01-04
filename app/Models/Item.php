<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    protected $connection = 'other';

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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
