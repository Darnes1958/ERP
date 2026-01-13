<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\BuyResource\Pages\BuyEdit;
use App\Filament\market\Resources\BuyResource\Pages\CreateBuy;
use App\Filament\market\Resources\BuyResource\Pages\EditBuy;
use App\Filament\market\Resources\BuyResource\Pages\ListBuys;
use App\Filament\market\Resources\BuyResource\Pages\TarBuy;

use App\Livewire\Traits\Raseed;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Recsupp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BuyResource extends Resource
{
    use Raseed;
    protected static ?string $model = Buy::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='تعديل فاتورة شراء';
    protected static string | \UnitEnum | null $navigationGroup='فواتير شراء';
    protected static ?int $navigationSort=2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مشتريات');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(false)
            ->defaultKeySort(false)
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
            ->recordActions([
                Action::make('buytran')
                    ->iconButton()
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->url(fn(Model $record) => self::getUrl('buyedit', ['record' => $record])),
              Action::make('tarbuy')
                ->iconButton()
                ->icon('heroicon-m-arrows-right-left')
                ->color('primary')
                ->url(fn(Model $record) => self::getUrl('tarbuy', ['record' => $record])),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('حذف فاتورة شراء')
                    ->modalDescription('هل انت متأكد من الغاء هذه الفاتورة ؟')
                    ->hidden(fn(Buy $record): bool =>
                     Buy_tran::where('buy_id',$record->id)->whereColumn('qs1','!=','q1')->orWhereColumn('qs2','!=','q2')->exists()
                     || !Auth::user()->can('الغاء مشتريات'))
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
            'index' => ListBuys::route('/'),
            'create' => CreateBuy::route('/create'),
            'edit' => EditBuy::route('/{record}/edit'),
            'buyedit' => BuyEdit::route('/{record}/buyedit'),
          'tarbuy' => TarBuy::route('/{record}/tarbuy'),
        ];
    }
}
