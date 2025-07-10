<?php

namespace App\Filament\Pages\Reports;

use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Setting;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepMakzoon extends Page implements HasTable

{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static string $view = 'filament.pages.reports.rep-makzoon';
    protected static ?string $navigationLabel='تقرير عن المخزون';
  protected static ?string $navigationGroup='مخازن و أصناف';
  protected static ?int $navigationSort=6;
    protected ?string $heading="";

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin');
  }




    public function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('الصفحات')
            ->query(function (Place_stock $place_stock){
                $place_stock=Place_stock::
                withSum('Item as buy_cost',DB::raw('stock1 * price_buy'))
                ->withSum('Item as sell_cost',DB::raw('stock1 * price1'));
                return $place_stock;
            })
            ->headerActions([
                Action::make('طباعة')
                ->url(route('pdfrepmak'))
            ])
            ->columns([
                TextColumn::make('Place.name')
                    ->sortable()
                    ->searchable()
                    ->label('المكان'),
                TextColumn::make('item_id')
                    ->sortable()
                    ->searchable()
                   ->label('رقم الصنف'),
                TextColumn::make('Item.name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الصنف'),
                TextColumn::make('Item.Unita.name')
                    ->sortable()
                    ->searchable()
                    ->label('الوحدة'),
                TextColumn::make('Item.stock1')
                 ->label('الرصيد الكلي'),
                TextColumn::make('stock1')
                  ->visible(Setting::find(Auth::user()->company)->many_place)
                    ->label(function (){
                        if (Setting::find(Auth::user()->company)->has_two) return 'الكمية (ك)';
                        else return 'الكمية';
                    }),
                TextColumn::make('stock2')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->label('الكمية (ص)'),
              TextColumn::make('Item.price_buy')
                ->visible(Auth::user()->can('ادخال مشتريات'))
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->label('سعر الشراء'),
              TextColumn::make('buy_cost')
               ->visible(Auth::user()->can('ادخال مشتريات'))
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->summarize(Sum::make()->label('')
                ->numeric(decimalPlaces: 2,thousandsSeparator: ',',decimalSeparator: '.'))
                ->label('تكلفة الشراء'),

              TextColumn::make('Item.price1')
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->label('سعر البيع'),
              TextColumn::make('sell_cost')
                ->visible(Auth::user()->can('ادخال مشتريات'))
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                  ->summarize(Sum::make()->label('')
                      ->numeric(decimalPlaces: 2,thousandsSeparator: ',',decimalSeparator: '.'))
                ->label('قيمة البيع'),
            ])
            ->filters([
             Filter::make('anyfilter')
                ->form([
                Checkbox::make('showZero')
                 ->label('اطهار الاصفار'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        ! $data['showZero'],
                        fn (Builder $query, $date): Builder => $query->where('stock1','!=',0),
                    );
                }),
             SelectFilter::make('place_id')
                    ->options(Place::all()->pluck('name', 'id'))
                    ->label('حسب المكان')
                    ->searchable()




            ], layout: FiltersLayout::AboveContent)

            ->striped();
    }
}
