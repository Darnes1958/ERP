<?php

namespace App\Models;

use App\Enums\ImpExp;
use App\Enums\RecWhoView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RecSuppUnion extends Model
{
    protected $connection = 'other';
    protected $table = 'rec_supp_unions';
    protected $primaryKey = false;
    public $timestamps = false;
    protected $casts =[
        'rec_who' => RecWhoView::class,

    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
