<?php

namespace App\Filament\Pages\Reports;

use Filament\Actions\Action;
use App\Livewire\Traits\PublicTrait;
use App\Models\Customer;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Setting;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
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
use Illuminate\Support\Facades\Response;

class RepMakzoon extends Page implements HasTable

{
    use InteractsWithTable;
    use PublicTrait;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';


    protected string $view = 'filament.pages.reports.rep-makzoon';
    protected static ?string $navigationLabel='تقرير عن المخزون';
  protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
  protected static ?int $navigationSort=6;
    protected ?string $heading="";

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin')  || Auth::user()->can('تقارير مخزون');
  }




    public function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('الصفحات')
            ->query(function (Place_stock $place_stock){
                $place_stock=Place_stock::
                withSum('Item as buy_cost',DB::raw('stock1 * price_buy'))
                ->withSum('Item as sell_cost',DB::raw('stock1 * price1'))
                ;
                return $place_stock;
            })
            ->headerActions([
                Action::make('طباعة')
                    ->action(function (){
                        $filters=$this->table->getFilters();

                        $res=$this->getTableQueryForExport()->get();
                        if ($res->count()==0) return ;

                        if ($filters['place_id']->getState()['value'])
                            $place=Place::find($filters['place_id']->getState()['value'])->name;
                        else $place=null;


                        return Response::download(self::ret_spatie($res,
                            'PDF.pdf-rep-makzoone',[
                                'place'=>$place,'show'=>true,
                            ]
                        ), 'filename.pdf', self::ret_spatie_header());

                    }),
                Action::make('print_without')
                    ->label('طباعة بدون سعر الشراء')
                    ->action(function (){
                        $filters=$this->table->getFilters();

                        $res=$this->getTableQueryForExport()->get();
                        if ($res->count()==0) return ;

                        if ($filters['place_id']->getState()['value'])
                            $place=Place::find($filters['place_id']->getState()['value'])->name;
                        else $place=null;


                        return Response::download(self::ret_spatie($res,
                            'PDF.pdf-rep-makzoone',[
                                'place'=>$place,'show'=>false,
                            ]
                        ), 'filename.pdf', self::ret_spatie_header());

                    })
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

                TextColumn::make('Item.stock1')
                 ->label('الرصيد الكلي'),
                TextColumn::make('stock1')
                  ->visible(Setting::find(Auth::user()->company)->many_place)
                    ->label( 'رصيد المكان'),

              TextColumn::make('Item.price_buy')
                ->visible(Auth::user()->can('ادخال مشتريات'))
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->label('سعر الشراء'),

                TextColumn::make('place_buy_cost')
                    ->label('تكلفة الشراء للمكان')
                    ->numeric(decimalPlaces: 2,thousandsSeparator: ',',decimalSeparator: '.')
                    ->summarize(Summarizer::make()
                        ->using(fn ($query): string =>
                        $query->join('items','item_id','items.id')->sum(DB::Raw('items.price_buy*place_stocks.stock1')))
                        ->numeric(decimalPlaces: 2,thousandsSeparator: ',',decimalSeparator: '.')),
              TextColumn::make('Item.price1')
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->label('سعر البيع'),
              TextColumn::make('place_sell_cost')
                  ->summarize(Summarizer::make()
                      ->using(fn ($query): string =>
                      $query->join('items','item_id','items.id')->sum(DB::Raw('items.price1*place_stocks.stock1')))
                      ->numeric(decimalPlaces: 2,thousandsSeparator: ',',decimalSeparator: '.'))

                ->visible(Auth::user()->can('ادخال مشتريات'))
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->label('قيمة البيع'),
            ])
            ->filters([
             Filter::make('anyfilter')
                ->schema([
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
