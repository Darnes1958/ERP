<?php

namespace App\Filament\Pages;

use App\Livewire\Traits\Raseed;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Tar_sell;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Tar_sell_Page extends Page implements HasTable
{
    use InteractsWithTable;
    use Raseed;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected ?string $heading='ترجيع المبيعات';
    protected static ?string $navigationLabel='عرض والغاء مبيعات';
    protected static ?string $navigationGroup='فواتير مبيعات';
    protected static ?int $navigationSort=6;
    protected static string $view = 'filament.pages.tar_sell_-page';
    public  function table(Table $table): Table
    {
        return $table
            ->query(function (Tar_sell $sell_tran)  {
                $sell_tran=Tar_sell::query();

                return  $sell_tran;
            })
            ->defaultSort('tar_date','desc')
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->label('الرقم الألي')
                    ->sortable(),
                TextColumn::make('tar_date')
                    ->searchable()
                    ->label('التاريخ')
                    ->sortable(),
                TextColumn::make('sell_id')
                    ->searchable()
                    ->label('رقم فاتورة المبيعات')
                    ->sortable(),
                TextColumn::make('Sell.Customer.name')
                    ->searchable()
                    ->label('إسم الزبون'),
                TextColumn::make('Item.name')
                    ->searchable()
                    ->label('اسم الصنف')
                    ->sortable(),
                TextColumn::make('q1')
                    ->searchable()
                    ->numeric()
                    ->label('الكمية')
                    ->sortable(),
                TextColumn::make('p1')
                    ->searchable()
                    ->label('سعر الصنف')
                    ->sortable(),

            ])

            ->actions([
                Action::make('del')
                    ->label('إلغاء الترجيع')
                    ->requiresConfirmation()
                    ->visible(function (Tar_sell $record){
                        return $record->Sell()->exists();
                    })
                    ->action(function (Tar_sell $record){

                        $selltran=Sell_tran::where('sell_id',$record->sell_id)->where('item_id',$record->item_id)->first()  ;
                        $tarsell=$record;
                        $sell=Sell::find($record->sell_id);

                        $this->incAll($record->sell_id,$selltran->item_id,$sell->place_id,$selltran->q1,
                            $selltran->q2);
                        $selltran->q1+=$tarsell->q1;
                        $selltran->tar_sell_id=null;
                        $selltran->sub_tot+=$tarsell->sub_tot;
                        $selltran->save();
                        $this->decAll($selltran->id,$record->sell_id,$selltran->item_id,
                            $sell->place_id,$selltran->q1,$selltran->q2);

                        $tot = Sell_tran::where('sell_id', $record->sell_id)->sum('sub_tot');
                        $sell->tot=$tot;
                        $sell->differ=($sell->tot+$sell->cost)*$sell->rate/100;
                        $sell->total=$tot+$sell->differ+$sell->cost;
                        $sell->baky=$sell->total-$sell->pay;
                        $sell->save();

                        $tarsell->delete();


                    })
            ])
            ;
    }
}
