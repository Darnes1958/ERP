<?php

namespace App\Filament\ins\Pages\Reports;

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


class RepPlace extends Page implements HasTable, HasForms
{

    use InteractsWithTable,InteractsWithForms;

  protected ?string $heading = '';

   public static ?string $title = 'إجمالي الفروع';

  protected static string | \UnitEnum | null $navigationGroup='تقارير';
  protected static ?int $navigationSort=4;


  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('اجمالي المصارف');
  }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.reports.rep-bank';

    public $Place;

    public function mount(){
        $this->Place=Place::first()->id;
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
              $bank=  Taj::selectRaw('TajName,tajs.id,
              isnull(sum(sul),0) sul,count(*) count, isnull(sum(mains.pay),0) pay,
              isnull(sum(mains.raseed),0) raseed,dbo.ret_ratio(tajs.id,'.$this->Place.')   val')
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
                TextColumn::make('count')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->label('')->numeric(0,'',','))
                    ->label('عدد العقود'),
                TextColumn::make('sul')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->label('')->numeric('2','.',','))
                    ->label('اجمالي العقود'),
                TextColumn::make('pay')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->label('')->numeric('2','.',','))
                    ->label('المسدد'),
                TextColumn::make('raseed')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->label('')->numeric('2','.',','))
                    ->label('الباقي'),

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
