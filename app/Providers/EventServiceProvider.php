<?php

namespace App\Providers;

use App\Livewire\Traits\AksatTrait;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Main;
use App\Models\Taj;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    use AksatTrait;
    public function boot(): void
    {
        Taj::creating(function($blog){
            $blog->user_id = auth()->id();
        });

        Taj::updating(function($blog){
            $blog->user_id = auth()->id();
        });
        Bank::creating(function($blog){
            $blog->user_id = auth()->id();
        });

        Bank::updating(function($blog){
            $blog->user_id = auth()->id();
        });
        Customer::creating(function($blog){
            $blog->user_id = auth()->id();
        });

        Customer::updating(function($blog){
            $blog->user_id = auth()->id();
        });
        Main::creating(function($blog){
            $blog->user_id = auth()->id();
            $blog->sul_end = date('Y-m-d', strtotime($blog->sul_begin . "+".$blog->kst_count." month"));
            $blog->NextKst=$this->setMonth($blog->sul_begin);
            $blog->LastUpd=now();
            $blog->kst_baky=$blog->kst_count;
            $blog->raseed=$blog->sul-$blog->pay;
            $blog->taj_id=Bank::find($blog->bank_id)->taj_id;
        });

        Main::updating(function($blog){
            info($blog);
            $blog->user_id = auth()->id();
            $blog->taj_id=Bank::find($blog->bank_id)->taj_id;


        });

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
