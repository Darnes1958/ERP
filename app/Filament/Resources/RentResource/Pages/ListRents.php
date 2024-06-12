<?php

namespace App\Filament\Resources\RentResource\Pages;

use App\Filament\Resources\RentResource;
use App\Models\Rent;
use App\Models\Renttran;
use App\Livewire\Traits\AksatTrait;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRents extends ListRecords
{
    use AksatTrait;
    protected static string $resource = RentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة'),
            Actions\Action::make('إدارج_إيجار')
                ->color('success')
                ->modalSubmitActionLabel('إدراج')

                ->form([
                    DatePicker::make('month')
                        ->label('عن شهر')
                        ->required()
                        ->native(false)
                        ->displayFormat('Y/m')
                        ->format('Y/m')
                        ->closeOnDateSelection(),

                ])
                ->action(function (array $data) {
                    if (! Renttran::where('month',$data['month'])->exists())
                    {
                        $main=Rent::where('status',1)->get();

                        foreach ($main as $item) {
                            $tran=new Renttran;
                            $tran->rent_id=$item->id;
                            $tran->tran_date=now();
                            $tran->tran_type='إيجار';
                            $tran->val=$item->amount;
                            $tran->notes='إيجار شهر '.$data['month'];
                            $tran->month=$data['month'];
                            $tran->save();
                        }
                        $this->TarseedRents();
                        Notification::make()
                            ->title('تم إدراج الإيجار بنجاح ')
                            ->icon('heroicon-o-check')
                            ->duration(5000)
                            ->iconColor('success')
                            ->send();

                    }
                    else
                        Notification::make()
                            ->title('سبق إدراج هذا الإيجار')
                            ->body('يرجي مراجعة الإيجارت المدخلة سابقا')
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->duration(10000)
                            ->iconColor('danger')
                            ->send();
                }),
            Actions\Action::make('سحب')
                ->color('success')
                ->icon('heroicon-o-minus-circle')
                ->form([
                    Select::make('rent_id')
                        ->label('الاسم')
                        ->options(Rent::all()->pluck('name','id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('tran_date')
                        ->required()
                        ->default(now())
                        ->label('التاريخ'),
                    TextInput::make('val')
                        ->label('المبلغ')
                        ->required(),
                    TextInput::make('notes')
                        ->label('ملاحظات'),

                ])
                ->action(function (array $data) {

                    $tran=new Renttran;
                    $tran->rent_id=$data['rent_id'];
                    $tran->tran_date=$data['tran_date'];;
                    $tran->tran_type='سحب';
                    $tran->val=$data['val'];
                    $tran->notes=$data['notes'];
                    $tran->month='0';
                    $tran->save();

                    $this->TarseedRents();
                    Notification::make()
                        ->title('تم عملية السحب بنجاح ')
                        ->icon('heroicon-o-check')
                        ->duration(5000)
                        ->iconColor('success')
                        ->send();

                }),
        ];
    }
}
