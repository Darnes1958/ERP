<?php

namespace App\Filament\Resources\RentResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\RentResource;
use App\Models\Acc;
use App\Models\Kazena;
use App\Models\Rent;
use App\Models\Renttran;
use App\Livewire\Traits\AksatTrait;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRents extends ListRecords
{
    use AksatTrait;
    protected static string $resource = RentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة'),
            Action::make('إدارج_إيجار')
                ->color('success')
                ->modalSubmitActionLabel('إدراج')

                ->schema([
                    DatePicker::make('month')
                        ->label('عن شهر')
                        ->required()
                        ->native(false)
                        ->displayFormat('Y/m')
                        ->format('Y/m')
                        ->closeOnDateSelection(),
                    DatePicker::make('tran_date')
                        ->required()
                        ->default(now())
                        ->label('التاريخ'),

                ])
                ->action(function (array $data) {
                    if (! Renttran::where('month',$data['month'])->exists())
                    {
                        $main=Rent::where('status',1)->get();

                        foreach ($main as $item) {
                            $tran=new Renttran;
                            $tran->rent_id=$item->id;
                            $tran->tran_date=$data['tran_date'];
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
            Action::make('سحب')
                ->color('success')
                ->icon('heroicon-o-minus-circle')
                ->schema([
                    Radio::make('pay_type')
                        ->options([
                            1=>'نقدا',
                            2=>'مصرفي',
                        ])
                        ->live()
                        ->default(1)
                        ->label('طريقة الدفع'),
                    Select::make('rent_id')
                        ->label('الاسم')
                        ->options(Rent::all()->pluck('name','id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('acc_id')
                        ->label('المصرف')
                        ->options(Acc::all()->pluck('name','id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->preload()
                        ->visible(fn(Get $get): bool =>($get('pay_type')==2 )),
                    Select::make('kazena_id')
                        ->label('الخزينة')
                        ->options(Kazena::all()->pluck('name','id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->preload()
                        ->default(function (){
                            $res=Kazena::where('user_id',Auth::id())->first();
                            if ($res) return $res->id;
                            else return null;
                        })
                        ->visible(fn(Get $get): bool =>($get('pay_type')==1 )),
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
                    if ($data['pay_type']==2) $tran->acc_id=$data['acc_id'];
                    else $tran->kazena_id=$data['kazena_id'];
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
