<?php

namespace App\Filament\ins\Pages\Reports;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Overkst;
use App\Models\Place;
use App\Models\Taj;
use App\Models\Tarkst;
use App\Models\Wrongkst;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class RepPlaceRatio extends Page implements HasTable, HasForms
{

    use InteractsWithTable,InteractsWithForms;

  protected ?string $heading = '';

   public static ?string $title = 'عمولة المصارف';

  protected static string | \UnitEnum | null $navigationGroup='تقارير';
  protected static ?int $navigationSort=4;


  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('اجمالي المصارف');
  }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.reports.rep-bank';

    public $Place;
    public $date1;
    public $date2;

    public function mount(){
        $this->Place=Place::first()->id;
        $date=Carbon::now();
        $this->date1=$date->copy()->startOfYear();
        $this->date2=now();
        $this->form->fill(['Place'=>$this->Place,'date1'=>$this->date1,'date2'=>$this->date2]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('Place')
                    ->options(Place::all()->pluck('name', 'id'))
                    ->live()
                    ->label('الصالة أو المخزن')
                    ->columnSpan(2)
                    ->afterStateUpdated(function ($state){
                        $this->Place=$state;
                    }),
                DatePicker::make('date1')
                 ->live()
                 ->afterStateUpdated(function ($state){
                     $this->date1=$state;
                 })
                 ->label('من تاريخ'),
                DatePicker::make('date2')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->date2=$state;
                    })
                    ->label('حتي تاريخ'),

            ])->columns(6);
    }
    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }
    public function table(Table $table):Table
    {
        return $table

            ->query(function ()  {
              $bank=  Taj::selectRaw('TajName,dbo.ret_ratio_period(tajs.id,'.$this->Place.'
              ,\''.Carbon::parse($this->date1)->format('d-m-Y').'\'
              ,\''.Carbon::parse($this->date2)->format('d-m-Y').'\')   val')
                  ->join('mains','mains.taj_id','=','Tajs.id')
                  ->join('sells','sells.id','=','mains.sell_id')
                  ->where('sells.place_id','=',$this->Place)
                  ->groupby('TajName','tajs.id')
                ;
                return  $bank;
            })
            ->pluralModelLabel('المصارف')
            ->columns([

                TextColumn::make('TajName')
                    ->label('المصرف التجميعي'),

                TextColumn::make('val')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->label('')->numeric('2','.',','))
                    ->label('عمولة المصرف'),
            ])

            ;
    }
}
