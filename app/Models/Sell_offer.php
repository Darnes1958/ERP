<?php

namespace App\Models;

use App\Enums\Jomla;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sell_offer extends Model
{
    protected $connection = 'other';

    protected $casts = [
        'Jomla' => Jomla::class,
    ];

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

    public function Sell_offer_tran()
    {
        return $this->hasMany(Sell_offer_tran::class, 'sell_id');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection = Auth::user()->company;
        }
    }
}
