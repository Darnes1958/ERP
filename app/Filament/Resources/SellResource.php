<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellResource\Pages;
use App\Filament\Resources\SellResource\RelationManagers;
use App\Livewire\Traits\Raseed;
use App\Models\Buy_tran;
use App\Models\BuySell;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SellResource extends Resource
{

    use Raseed;
    protected static ?string $model = Sell::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='تعديل فاتورة مبيعات';
    protected static ?string $navigationGroup='فواتير مبيعات';
    protected static ?int $navigationSort=2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('تعديل مبيعات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function TwoToOne($count,$q1,$q2){
        return $q2+($q1*$count);
    }
    public static function  incQs($sell_id,$item,$count){

        $buysell= BuySell::where('sell_id',$sell_id)
            ->where('item_id',$item)
            ->get();
        foreach ($buysell as $tran) {
            $two_unit=Item::find($item)->two_unit;

            if ($two_unit->value==1)
                $q=static::TwoToOne($count,$tran->q1,$tran->q2);
            else $q=$tran->q1;

            $Buy=Buy_tran::where('buy_id',$tran->buy_id)
                ->where('item_id',$item)->first();

            if ($two_unit->value==1)
                $qs=$q+static::TwoToOne($count,$Buy->qs1,$Buy->qs2);
            else $qs=$q+$Buy->qs1;


            Buy_tran::where('buy_id',$tran->buy_id)
                ->where('item_id',$item)->update([
                    'qs1'=>$qs,
                ]);

        }
        BuySell::where('sell_id',$sell_id)
            ->where('item_id',$item)
            ->delete();
    }
    public static function incAll($sell_id,$item_id,$place_id,$q1,$q2) {

        $item=Item::find($item_id);
        $count=$item->count;
        $two_unit=$item->two_unit;

        if ($two_unit->value==1){
            $quant=$q2+($q1*$count);
            $quantItem=($item->stock2+($item->stock1*$count)) + $quant;
            $item->stock1=intdiv($quantItem,$count);
            $item->stock2=$quantItem%$count;
        } else $item->stock1+=$q1;
        $item->save();

        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
        if ($two_unit->value==1) {
            $quantPlace = ($place->stock2 + ($place->stock1 * $count)) + $quant;
            $place->stock1 = intdiv($quantPlace, $count);
            $place->stock2 = $quantPlace % $count;
        } else $place->stock1+=$q1;
        $place->save();

        static::incQs($sell_id,$item_id,$count);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id','desc')
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->label('الرقم الالي'),
                TextColumn::make('Customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الزبون'),
                TextColumn::make('order_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('tot')
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('cost')
                    ->searchable()
                    ->sortable()
                    ->label('تكاليف إضافية'),
                TextColumn::make('differ')
                    ->searchable()
                    ->sortable()
                    ->label('فرق عملة'),
                TextColumn::make('total')
                    ->searchable()
                    ->sortable()
                    ->label('الإجمالي النهائي'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('الباقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('selltran')
                    ->iconButton()
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->url(fn(Model $record) => self::getUrl('selledit', ['record' => $record])),
                Tables\Actions\Action::make('tarsell')
                    ->iconButton()
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('primary')
                    ->url(fn(Model $record) => self::getUrl('tarsell', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(Auth::user()->can('الغاء مبيعات'))
                    ->modalHeading('حذف فاتورة مبيعات')
                    ->modalDescription('هل انت متأكد من الغاء هذه الفاتورة ؟')
                    ->before(function(Sell $record) {
                        $selltran=Sell_tran::where('sell_id',$record->id)->get();
                        foreach ($selltran as $tran) {
                            static::incAll($record->id,$tran->item_id,$record->place_id,$tran->q1,$tran->q2);
                        }
                        Receipt::where('sell_id',$record->id)->delete();
                        Sell_tran::where('sell_id',$record->id)->delete();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSells::route('/'),
            'selledit' => Pages\SellEdit::route('/{record}/selledit'),
            'create' => Pages\CreateSell::route('/create'),
            'edit' => Pages\EditSell::route('/{record}/edit'),
            'tarsell' => Pages\TarSell::route('/{record}/tarsell'),
        ];
    }
}
