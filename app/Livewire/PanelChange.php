<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Schmeits\FilamentPhosphorIcons\Support\Icons\Phosphor;
use Schmeits\FilamentPhosphorIcons\Support\Icons\PhosphorWeight;

class PanelChange extends Component implements HasForms
{
    use InteractsWithForms;
    public $thePanel;

    public function mount()
    {
      $this->thePanel=Filament::getCurrentPanel()->getPath();
    }


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ToggleButtons::make('thePanel')
                    ->hiddenLabel()
                    ->live()
                    ->options(function (){
                     if (Auth::user()->is_prog)
                         return   [
                             'market' => 'مبيعات',
                             'ins' => 'أقساط',
                             'admin' => 'Admin',
                         ];
                     else
                         return    [
                             'market' => 'مبيعات',
                             'ins' => 'أقساط',

                         ];
                    })
                    ->colors([
                        'market' => 'info',
                        'admin'   => 'warning',
                        'ins'     => 'success',
                    ])
                    ->icons([
                        'market' => Phosphor::ShoppingCart->getIconForWeight(PhosphorWeight::Bold),
                        'admin'  => Heroicon::User,
                        'ins'    => Phosphor::Coins,
                    ])
                    ->afterStateUpdated(function ($state) {
                        $this->thePanel=$state;
                        redirect(Filament::getPanel($state)->getPath());
                    })
                    ->inline()
            ]);
    }

    public function render()
    {
        return view('livewire.panel-change');
    }
}
