<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sell_offer_work extends Model
{
    protected $connection = 'other';

    public $incrementing = false;

    public function Customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function Price_type()
    {
        return $this->belongsTo(Price_type::class);
    }

    public function Place()
    {
        return $this->belongsTo(Place::class);
    }

    public function Sell_offer_tran_work()
    {
        return $this->hasMany(Sell_offer_tran_work::class, 'sell_id');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection = Auth::user()->company;
        }
    }
}
