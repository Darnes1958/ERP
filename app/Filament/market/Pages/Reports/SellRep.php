<?php

namespace App\Filament\market\Pages\Reports;

use App\Livewire\Traits\PublicTrait;
use App\Models\Customer;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Place;
use App\Models\Sell;
use App\Models\Sell_tran;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;


class SellRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;
  use HasTabs;
  use PublicTrait;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.market.pages.reports.sell-rep';
    protected static ?string $navigationLabel = 'تقرير فواتير مبيعات';
    protected static string | \UnitEnum | null $navigationGroup = 'فواتير مبيعات';
    protected static ?int $navigationSort=3;
    protected ?string $heading = "";

    public function mount(): void
    {
        $this->loadDefaultActiveTab();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مبيعات') || Auth::user()->can('تقارير مبيعات');
    }


    public function table(Table $table): Table
 {
   return $table
    ->query(function (){
      ;
      return Sell::query();
    })
       ->headerActions([
           Action::make('print1')
               ->label('طباعة')
               ->action(function (){
                   $filters=$this->table->getFilters();
                   $res=$this->getTableQueryForExport()->get();
                   if ($res->count()==0) return ;

                   if ($filters['place_id']->getState()['value'])
                    $place=Place::find($filters['place_id']->getState()['value'])->name;
                   else $place=null;
                   if ($filters['customer_id']->getState()['value'])
                       $customer=Customer::find($filters['customer_id']->getState()['value'])->name;
                   else $customer=null;

                   $any=$filters['created_at']->getState();
                   $RepDate1=$any['Date1'] ; $RepDate2=$any['Date2'];

                   $active=$this->activeTab;


                   return Response::download(self::ret_spatie($res,
                       'PDF.pdf-rep-sell',[
                           'RepDate1'=>$RepDate1,
                           'RepDate2'=>$RepDate2,
                           'place'=>$place,
                           'customer'=>$customer,
                           'active'=>$active,
                           ]
                       ), 'filename.pdf', self::ret_spatie_header());

               })

       ])
     ->pluralModelLabel('الصفحات')
     ->striped()
     ->defaultKeySort(false)
     ->defaultSort('id','desc')
     ->columns([
         TextColumn::make('ت')
       ->rowIndex(),
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
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
           ->summarize(Sum::make()->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->label('اجمالي الفاتورة'),
       TextColumn::make('cost')
         ->searchable()
         ->sortable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
           ->summarize(Sum::make()->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->label('تكاليف إضافية'),
       TextColumn::make('differ')
         ->searchable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
           ->summarize(Sum::make()->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->sortable()
         ->label('فرق عملة'),
       TextColumn::make('total')
         ->searchable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
           ->summarize(Sum::make()->label('')->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->sortable()
         ->label('الإجمالي النهائي'),

       TextColumn::make('pay')
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
           ->summarize(Sum::make()->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->label('المدفوع'),
       TextColumn::make('baky')
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
           ->summarize(Sum::make()->label('')->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           ))
         ->label('الباقي'),
         TextColumn::make('sell_tran_sum_profit')
             ->visible(Auth::user()->hasRole('admin'))
             ->sum('Sell_tran','profit')
             ->summarize(Sum::make()->label('')->numeric(
                 decimalPlaces: 2,
                 decimalSeparator: '.',
                 thousandsSeparator: ',',
             ))
             ->label('الربح'),
       TextColumn::make('notes')
         ->label('ملاحظات'),

     ])

     ->recordActions([
       Action::make('عرض ')
         ->modalHeading(false)
         ->modalSubmitAction(false)
         ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
         ->modalContent(fn (Sell $record): View => view(
           'filament.market.pages.reports.views.view-sell-tran-widget',
           ['sell_id' => $record->id],
         ))
         ->icon('heroicon-o-eye')
         ->iconButton(),
         Action::make('print')
             ->icon('heroicon-o-printer')
             ->iconButton()
             ->color('blue')
             ->action(function (Sell $record) {
                 $sell=$record;
                 $res=Sell_tran::where('sell_id',$record->id)->get();
                 return Response::download(self::ret_spatie($res,
                     'PDF.rep-order-sell',[
                         'sell'=>$sell,
                     ]
                 ), 'filename.pdf', self::ret_spatie_header());

             })

     ])
     ->modifyQueryUsing($this->modifyQueryWithActiveTab(...))
     ->filtersFormWidth(Width::Small)

     ->filters([
       SelectFilter::make('customer_id')
         ->options(Customer::all()->pluck('name', 'id'))
         ->searchable()
         ->label('زبون معين'),
       SelectFilter::make('place_id')
             ->options(Place::all()->pluck('name', 'id'))
             ->searchable()
             ->label('نقطة بيع معينة'),
       Filter::make('created_at')
         ->schema([
           DatePicker::make('Date1')
             ->label('من تاريخ'),
           DatePicker::make('Date2')
             ->label('إلي تاريخ'),
         ])

         ->indicateUsing(function (array $data): ?string {
           if (! $data['Date1'] && ! $data['Date2']) { return null;   }
           if ( $data['Date1'] && !$data['Date2'])
             return 'ادخلت بتاريخ  ' . Carbon::parse($data['Date1'])->toFormattedDateString();
           if ( !$data['Date1'] && $data['Date2'])
             return 'حتي تاريخ  ' . Carbon::parse($data['Date2'])->toFormattedDateString();
           if ( $data['Date1'] && $data['Date2'])
             return 'ادخلت في الفترة من  ' . Carbon::parse($data['Date1'])->toFormattedDateString()
               .' إلي '. Carbon::parse($data['Date1'])->toFormattedDateString();

         })
         ->query(function (Builder $query, array $data): Builder {
           return $query
             ->when(
               $data['Date1'],
               fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
             )
             ->when(
               $data['Date2'],
               fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
             );
         })

     ]);
 }
    public function getTabs(): array
    {
        return [
            'الكل' => Tab::make(),
            'تقسيط' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', 3)),
            'تقسيط قائم' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', 3)
                ->whereIn('id', Main::query()->pluck('sell_id'))),
            'تقسيط أرشيف' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', 3)
                    ->whereIn('id', Main_arc::query()->pluck('sell_id'))),
            'تقسيط بدون عقد' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', 3)
                    ->whereNotIn('id', Main::query()->pluck('sell_id'))
                    ->whereNotIn('id', Main_arc::query()->pluck('sell_id'))),

            'نقداً' => Tab::make()

                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', '!=',3)),
            'نقداً آجلة' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', '!=',3)
                    ->where('baky','!=',0)),
            'نقداً مدفوعة' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('price_type_id', '!=',3)
                    ->where('baky',0)),
        ];
    }
    public function getDefaultActiveTab(): string | int | null
    {
        return 'الكل';
    }


}
