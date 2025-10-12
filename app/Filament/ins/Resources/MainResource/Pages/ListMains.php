<?php

namespace App\Filament\ins\Resources\MainResource\Pages;

use App\Filament\ins\Resources\MainResource;
use App\Filament\ins\Pages\newCont;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;


class ListMains extends ListRecords
{
    protected static string $resource = MainResource::class;

    public function getTitle():  string|Htmlable
    {
        return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">عقود</div>');
    }

    protected function getHeaderActions(): array
    {
        return [
           CreateAction::make()
                ->label('عقد جديد')
                ->visible( function (){

                  return Auth::user()->can('ادخال عقود')
                  && ! Setting::find(Auth::user()->company)->is_together;
                }

                ),

            Action::make('Maincreate')
                ->label('ادخال عقد')
                ->icon('heroicon-m-users')
                ->color('danger')

                ->visible( function (){
                  return  Auth::user()->can('ادخال عقود')
                    &&  Setting::find(Auth::user()->company)->is_together;
                }

                )
                ->url( newCont::getUrl()),
                //->url( 'mains/maincreate'),

        ];
    }
}
