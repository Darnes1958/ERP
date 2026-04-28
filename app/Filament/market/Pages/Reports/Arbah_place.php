<?php

namespace App\Filament\market\Pages\Reports;

use App\Livewire\widget\ChartArbah;
use App\Livewire\widget\RebhMonthPlace;
use App\Models\Place;
use App\Models\Rebh_first_place;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class Arbah_place extends Page implements HasForms,HasActions,HasTable
{
  use InteractsWithForms,InteractsWithActions,InteractsWithTable;
  protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'الارباح حسب الصالات';
  protected static string | \UnitEnum | null $navigationGroup = 'ادارة';
  protected static ?string $navigationParentItem='الارباح';
  protected static ?int $navigationSort=3;

  public function chkDate($repDate){
    try {
      Carbon::parse($repDate);
      return true;
    } catch (InvalidFormatException $e) {
      return false;
    }
  }
  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin');
  }
    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected string $view = 'filament.market.pages.reports.arbah-place';

  protected ?string $heading="";

  public $year;
  public $place;
    public $amma;
  public function mount(){
    $year=Rebh_first_place::first()->year;
    $this->place=Place::first()->id;
      $this->amma=Rebh_first_place::where('wyear',$this->year)->where('place_id',null)->sum('profit');
      if (!$this->amma) $this->amma=0;
   $this->form->fill([
       'year' => $year,'place' => $this->place,'amma' => $this->amma,
   ]);
  }
public function form(Schema $schema): Schema
{
    return $schema
        ->components([
           Select::make('year')
            ->options(Rebh_first_place::selectraw('distinct wyear as year')->pluck('year','year'))
            ->label('السنه')
            ->preload()
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state,Set $set){
                $this->year=$state;
                $this->dispatch('updateyearplace',year: $this->year,place: $this->place);
                $this->amma=Rebh_first_place::where('wyear',$this->year)->where('place_id',null)->sum('profit');
                $set('amma',$this->amma);
            }),
            Select::make('place')
                ->options(Place::all()->pluck('name','id'))
                ->label('المكان')
                ->preload()
                ->searchable()
                ->live()
                ->afterStateUpdated(function ($state){
                    $this->place=$state;
                    $this->dispatch('updateyearplace',year: $this->year,place: $this->place);
                }),
            TextInput::make('amma')
             ->label('مصروفات الإدارة العامة')
                ->default(0)
            ->readOnly(),
        ])->columns(4);
}
    public array $data_list= [
        'calc_columns' => [
            'rebh',
            'rent',
            'masr',
            'sal',
            'ksm',
            'safi',
        ],
    ];
    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (){

                $res=Rebh_first_place::selectRaw('
                wyear,
                wmonth ,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) rebh,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) masr,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) rent,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) sal,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'ksm\'),0) ksm,

                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'ksm\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) safi
                ')
                    ->Where('wyear',$this->year)
                    ->groupBy('wyear','wmonth')
                ;

                return $res;
            }

            )
            ->emptyStateHeading('لا توجد بيانات')
            ->heading(fn()=>new HtmlString(
                '<div class="text-primary-400 text-lg">'.'الارباح بالأشهر لسنه '.$this->year.'</div>'

            ))
            ->contentFooter(view('table.footerNoDecimal', $this->data_list))
            ->paginated([5,10,12])
            ->defaultPaginationPageOption(12)
            ->defaultSort('wmonth')
            ->columns([
                TextColumn::make('wmonth')
                    ->label('الشهر'),
                TextColumn::make('rebh')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('هامش الربح'),
                TextColumn::make('masr')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('مصروفات'),
                TextColumn::make('sal')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('مرتبات'),
                TextColumn::make('rent')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('ايجارات'),
                TextColumn::make('ksm')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('خصومات'),
                TextColumn::make('safi')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                    ->label('صافي الأرباح'),


            ]);
    }

 //   protected function getFooterWidgets(): array
 // {
 //   return [
//
 // //   RebhMonthPlace::make([
 // //     'year'=>$this->year,'place' => $this->place,
 // //   ]),
 //       ChartArbah::make(['year'=>$this->year,'place' => $this->place,])
//
//
//
 //   ];
 // }


}
