<?php

namespace App\Filament\ins\Pages\Reports;

use Filament\Schemas\Schema;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Overkst;
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


class RepBank extends Page implements HasTable, HasForms
{

    use InteractsWithTable,InteractsWithForms;

  protected ?string $heading = '';

   public static ?string $title = 'إجمالي المصارف';

  protected static string | \UnitEnum | null $navigationGroup='تقارير';
  protected static ?int $navigationSort=3;


  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('اجمالي المصارف');
  }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.reports.rep-bank';

    public $By=1;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('By')
                    ->options([
                        1 =>'بفروع المصارف',
                        2 =>'بالتجميعي',
                    ])
                    ->live()
                    ->inline()
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->afterStateUpdated(function ($state){
                        $this->By=$state;
                    }),

            ]);
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function ()  {
                if ($this->By==1)
                 $bank=  Bank::has('main');
                else $bank = Taj::has('main');
                return  $bank;
            })
            ->pluralModelLabel('المصارف')
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي'),
                TextColumn::make('BankName')
                    ->hidden(fn(): bool=>$this->By==2)
                    ->label('الاسم'),
                TextColumn::make('TajName')
                    ->hidden(fn(): bool=>$this->By==1)
                    ->label('المصرف التجميعي'),
                TextColumn::make('main_count')
                    ->counts('Main')
                    ->summarize(Sum::make()->label(''))

                    ->label('عدد العقود')
                ,
                TextColumn::make('main_sum_sul')
                    ->sum('Main','sul')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )

                    ->label('اجمالي العقود')
                ,
                TextColumn::make('main_sum_pay')
                    ->sum('Main','pay')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )

                    ->label('المسدد')
                ,
                TextColumn::make('main_sum_raseed')
                    ->sum('Main','raseed')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )

                    ->label('الرصيد')
                ,

                TextColumn::make('main_sum_over_kst')
                    ->sum('Main','over_kst')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )

                    ->label('الفائض')
                ,
                TextColumn::make('main_sum_tar_kst')
                    ->sum('Main','tar_kst')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )

                    ->label('الترجيع')
                ,
               TextColumn::make('wrong_kst')
                    ->state(function (Model $record){
                        if ($this->By==1) $id=$record->taj_id; else $id=$record->id;
                       return Wrongkst::where('taj_id',$id)->where('status',1)->sum('kst');
                    })

                    ->label('بالخطأ'),
            ])

            ;
    }
}
