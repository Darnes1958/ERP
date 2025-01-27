<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Table;

class Main extends Model
{

    protected $connection = 'other';

    protected $appends =['name'];


    public function Customer(){
        return $this->belongsTo(Customer::class);
    }
    public function getNameAttribute(){
        return $this->Customer->name;
    }

    public function Sell(){
        return $this->belongsTo(Sell::class);
    }




    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }

}
