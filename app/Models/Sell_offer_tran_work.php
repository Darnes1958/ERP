<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sell_offer_tran_work extends Model
{
    protected $connection = 'other';

    public function Sell_offer_work()
    {
        return $this->belongsTo(Sell_offer_work::class, 'sell_id');
    }

    public function Item()
    {
        return $this->belongsTo(Item::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection = Auth::user()->company;
        }
    }
}
