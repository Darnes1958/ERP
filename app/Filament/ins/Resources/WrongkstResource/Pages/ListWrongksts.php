<?php

namespace App\Filament\ins\Resources\WrongkstResource\Pages;

use App\Filament\ins\Resources\WrongkstResource;
use App\Livewire\Traits\PublicTrait;
use App\Models\Correct;
use App\Models\Taj;
use App\Models\Wrongkst;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;

class ListWrongksts extends ListRecords
{
    use PublicTrait;
    protected static string $resource = WrongkstResource::class;

    protected ?string $heading='أقساط واردة بالخطأ';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('طباعة')
                ->icon('heroicon-o-printer')
                ->color('blue')
                ->action(function (){
                    $filter=$this->table->getFilters();
              //     dd($filter['taj_id']->getState()['value']);
                    if ( $filter['taj_id']->getState()['value']===null) {
                        Notification::make()->title('يجب اختيار مصرف تجميعي')->danger()->icon(Heroicon::RectangleStack)->send();
                        return;
                    }

                    $arr=[];
                    $arr['date']=date('Y-m-d');
                    $arr['TajName']=Taj::find($filter['taj_id']->getState()['value'])->TajName;
                    $res=Wrongkst::where('taj_id',$filter['taj_id']->getState()['value'])->get();

                    return Response::download(self::ret_spatie($res,
                        'PrnView.pdf-wrong',$arr), 'filename.pdf', self::ret_spatie_header());

                }),

            Action::make('viewStats')
                ->label('عرض الأرشيف')
                ->visible(fn():bool =>Correct::count()>0)
                ->modalHeading('عرض الاقساط المصححة والمرجعة في الارشيف')
               // ->modalWidth('4xl') // Adjust size as needed
                ->modalSubmitAction(false) // Remove "Submit" button if it's view-only
                ->modalCancelActionLabel('عودة')
                ->modalContent(fn () => new HtmlString(
                    Blade::render('@livewire(\App\Livewire\widget\ViewCorrect::class)')
                ))

        ];
    }
}
