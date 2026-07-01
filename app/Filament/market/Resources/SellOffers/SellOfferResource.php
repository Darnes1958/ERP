<?php

namespace App\Filament\market\Resources\SellOffers;

use App\Filament\market\Resources\SellOffers\Pages\ListSellOffers;
use App\Filament\market\Resources\SellOffers\Pages\SellOfferEdit;
use App\Livewire\Traits\PublicTrait;
use App\Models\Sell_offer;
use App\Models\Sell_offer_tran;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SellOfferResource extends Resource
{
    use PublicTrait;

    protected static ?string $model = Sell_offer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'عرض وتعديل فواتير العرض';
    protected static string | \UnitEnum | null $navigationGroup = 'فواتير مبيعات';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('تعديل مبيعات');
    }

    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()->hasRole('admin')) {
            return Sell_offer::query();
        }

        return parent::getEloquentQuery()->where('place_id', Auth::user()->place_id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (Auth::user()->hasRole('admin')) {
                    return Sell_offer::query();
                }

                return Sell_offer::where('place_id', Auth::user()->place_id);
            })
            ->defaultKeySort(false)
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('لا توجد فواتير عرض')
            ->recordUrl(false)
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
                    ->label('تكاليف إضافية'),
                TextColumn::make('differ')
                    ->label('فرق عملة'),
                TextColumn::make('total')
                    ->label('الإجمالي النهائي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->recordActions([
                Action::make('selltran')
                    ->iconButton()
                    ->tooltip('تعديل')
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->url(fn (Model $record) => self::getUrl('offeredit', ['record' => $record])),
                Action::make('print')
                    ->iconButton()
                    ->tooltip('طباعة')
                    ->icon('heroicon-o-printer')
                    ->color('blue')
                    ->action(function (Sell_offer $record) {
                        $res = Sell_offer_tran::where('sell_id', $record->id)->get();
                        return Response::download(
                            self::ret_spatie($res, 'PDF.rep-order-sell-offer', ['sell' => $record]),
                            'offer-'.$record->id.'.pdf',
                            self::ret_spatie_header()
                        );
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('الغاء')
                    ->visible(fn () => Auth::user()->can('الغاء مبيعات'))
                    ->modalHeading('حذف فاتورة عرض')
                    ->modalDescription('هل انت متأكد من الغاء فاتورة العرض هذه ؟')
                    ->before(function (Sell_offer $record) {
                        Sell_offer_tran::where('sell_id', $record->id)->delete();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSellOffers::route('/'),
            'offeredit' => SellOfferEdit::route('/{record}/offeredit'),
        ];
    }
}
