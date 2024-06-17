<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuyResource\Pages;
use App\Filament\Resources\BuyResource\RelationManagers;
use App\Livewire\Traits\Raseed;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Buys_work;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Recsupp;
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
use Termwind\Components\Raw;

class BuyResource extends Resource
{
    use Raseed;
    protected static ?string $model = Buy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='تعديل فاتورة شراء';
    protected static ?string $navigationGroup='فواتير شراء';
    protected static ?int $navigationSort=2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مشتريات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
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
                TextColumn::make('Supplier.name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المورد'),
                TextColumn::make('order_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('tot')
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي الفاتورة'),
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
                Tables\Actions\Action::make('buytran')
                    ->iconButton()
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->url(fn(Model $record) => self::getUrl('buyedit', ['record' => $record])),
              Tables\Actions\Action::make('tarbuy')
                ->iconButton()
                ->icon('heroicon-m-arrows-right-left')
                ->color('primary')
                ->url(fn(Model $record) => self::getUrl('tarbuy', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('حذف فاتورة شراء')
                    ->modalDescription('هل انت متأكد من الغاء هذه الفاتورة ؟')
                    ->hidden(fn(Buy $record)=>
                     Buy_tran::where('buy_id',$record->id)->whereColumn('q1','!=','q1')->whereColumn('q2','!=','q2')->exists())
                    ->before(function(Buy $record) {
                        $buytran=Buy_tran::where('buy_id',$record->id)->get();
                        foreach ($buytran as $tran) {
                            $item=Item::find($tran->item_id);
                            $item->stock1-=$tran->q1;
                            $item->save();

                            $place=Place_stock::where('place_id',$record->place_id)->where('item_id',$tran->item_id)->first();
                            $place->stock1-=$tran->q1;
                            $place->save();
                        }
                        Recsupp::where('buy_id',$record->id)->delete();
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
            'index' => Pages\ListBuys::route('/'),
            'create' => Pages\CreateBuy::route('/create'),
            'edit' => Pages\EditBuy::route('/{record}/edit'),
            'buyedit' => Pages\BuyEdit::route('/{record}/buyedit'),
          'tarbuy' => Pages\TarBuy::route('/{record}/tarbuy'),
        ];
    }
}
