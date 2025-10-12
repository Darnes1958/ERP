<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;
use Filament\Models\Contracts\HasAvatar;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements FilamentUser
{

 use HasRoles;

    public function Place()
    {
        return $this->belongsTo(Place::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {

        if ($panel->getId() === 'admin') {
            if ($this->is_prog) return true;
            else redirect(Filament::getPanel('market')->getPath());
        }

      return  $this->status->value==1;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
        'ís_prog'=>'bool',
    ];
}
