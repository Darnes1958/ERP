<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\RecsuppResource\Pages\ListRecsupps;
use App\Filament\Resources\RecsuppResource\Pages\CreateRecsupp;
use App\Filament\Resources\RecsuppResource\Pages\EditRecsupp;
use App\Enums\RecWho;
use App\Filament\Resources\RecsuppResource\Pages;
use App\Filament\Resources\RecsuppResource\RelationManagers;
use App\Models\Acc;
use App\Models\Buy;
use App\Models\Kazena;
use App\Models\Place;
use App\Models\Recsupp;

use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\text;

class RecsuppResource extends Resource
{
    protected static ?string $model = Recsupp::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationLabel = 'ايصالات موردين';
  protected static string | \UnitEnum | null $navigationGroup = 'ايصالات قبض ودفع';
  protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال ايصالات موردين');
    }

  public static function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        Radio::make('rec_who')
          ->inline()
          ->inlineLabel(false)
          ->label('نوع الايصال')
          ->default(1)
          ->live()
          ->columnSpan(2)
          ->options(RecWho::class),
        TextInput::make('id')
         ->label('الرقم الألي')
         ->disabled()
         ->hidden(fn(string $operation)=>$operation=='create') ,
        Select::make('supplier_id')
          ->label('المورد')
          ->relationship('Supplier','name')
          ->searchable()
          ->required()
          ->live()
          ->preload()
          ->createOptionForm([
            Section::make('ادخال مورد جديد')
              ->schema([
                TextInput::make('name')
                  ->required()
                  ->label('الاسم'),
                TextInput::make('address')
                  ->label('العنوان'),
                TextInput::make('mdar')
                  ->label('مدار'),
                TextInput::make('libyana')
                  ->label('لبيانا'),
                Hidden::make('user_id')
                  ->default(Auth::id()),
              ])
          ])
          ->editOptionForm([
            Section::make('تعديل بيانات مورد')
              ->schema([
                TextInput::make('name')
                  ->required()
                  ->label('الاسم'),
                TextInput::make('address')
                  ->label('العنوان'),
                TextInput::make('mdar')
                  ->label('مدار'),
                TextInput::make('libyana')
                  ->label('لبيانا'),
                Hidden::make('user_id')
                  ->default(Auth::id()),

              ])->columns(2)
          ]),

        Select::make('buy_id')
          ->label('رقم الفاتورة')
          ->options(fn (Get $get): Collection => Buy::query()
            ->where('supplier_id', $get('supplier_id'))
            ->selectRaw('\'الرقم \'+str(id)+\' الإجمالي \'+str(tot)+\' بتاريخ \'+convert(varchar,order_date) as name,id')
            ->pluck('name', 'id'))
          ->searchable()
          ->requiredIf('rec_who',[3,4])
          ->visible(fn(Get $get): bool =>($get('rec_who')==3 || $get('rec_who') ==4))
          ->preload(),
        Select::make('price_type_id')
          ->label('طريقة الدفع')
          ->relationship('Price_type','name')
          ->preload()
          ->live()
          ->searchable()
          ->default(1)
          ->required(),
        DatePicker::make('receipt_date')
          ->label('التاريخ')
          ->default(now())
          ->required(),
        TextInput::make('val')
          ->label('المبلغ')
          ->required()
          ->numeric(),
        Select::make('acc_id')
              ->label('المصرف')
              ->relationship('Acc','name')
              ->searchable()
              ->required()
              ->live()
              ->preload()
              ->visible(fn(Get $get): bool =>($get('price_type_id')==2 ))
              ->createOptionForm([
                  Section::make('ادخال حساب مصرفي جديد')
                      ->schema([
                          TextInput::make('name')
                              ->label('اسم المصرف')
                              ->required()
                              ->autofocus()
                              ->columnSpan(2)
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])        ,
                          TextInput::make('acc')
                              ->label('رقم الحساب')
                              ->required()
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])  ,
                          TextInput::make('raseed')
                              ->label('رصيد بداية المدة')
                              ->numeric()
                              ->required()                          ,
                      ])
              ])
              ->editOptionForm([
                  Section::make('تعديل بيانات مورد')
                      ->schema([
                          TextInput::make('name')
                              ->label('اسم المصرف')
                              ->required()
                              ->autofocus()
                              ->columnSpan(2)
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])        ,
                          TextInput::make('acc')
                              ->label('رقم الحساب')
                              ->required()
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])  ,
                          TextInput::make('raseed')
                              ->label('رصيد بداية المدة')
                              ->numeric()
                              ->required()

                      ])->columns(2)
              ]),
          Select::make('kazena_id')
              ->label('الخزينة')
              ->relationship('Kazena','name')
              ->searchable()
              ->required()
              ->live()
              ->preload()

              ->disabled(function () {return Kazena::where('user_id',Auth::id())->first();})
              ->default(function (){
                  $res=Kazena::where('user_id',Auth::id())->first();
                  if ($res) return $res->id;
                  else return null;
              })
              ->visible(fn(Get $get): bool =>($get('price_type_id')==1 ))
              ->createOptionForm([
                  Section::make('ادخال حساب خزينة جديد')
                      ->schema([
                          TextInput::make('name')
                              ->label('اسم الخزينة')
                              ->required()
                              ->autofocus()
                              ->columnSpan(2)
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])        ,
                          Select::make('user_id')
                              ->label('المستخدم')
                              ->searchable()
                              ->preload()
                              ->options(User::
                              where('company',Auth::user()->company)
                                  ->where('id','!=',1)
                                  ->pluck('name','id')),
                          TextInput::make('balance')
                              ->label('رصيد بداية المدة')
                              ->numeric()
                              ->required()                          ,
                      ])
              ])
              ->editOptionForm([
                  Section::make('تعديل بيانات خزينة')
                      ->schema([
                          TextInput::make('name')
                              ->label('اسم الخزينة')
                              ->required()
                              ->autofocus()
                              ->columnSpan(2)
                              ->unique(ignoreRecord: true)
                              ->validationMessages([
                                  'unique' => ' :attribute مخزون مسبقا ',
                              ])        ,
                          Select::make('user_id')
                              ->label('المستخدم')
                              ->searchable()
                              ->preload()
                              ->options(User::
                              where('company',Auth::user()->company)
                                  ->where('id','!=',1)
                                  ->pluck('name','id')),
                          TextInput::make('raseed')
                              ->label('رصيد بداية المدة')
                              ->numeric()
                              ->required()

                      ])->columns(2)
              ]),
          Select::make('place_id')
              ->label('المكان')
              ->relationship('Place','name')
              ->searchable()
              ->requiredIf('rec_who',4)
              ->live()
              ->preload()
              ->Visible(function () {return !Auth::user()->place_id;})
              ->default(function (){
                  if (Auth::user()->place_id) return Auth::user()->place_id;
                  else return null;
              }),
        TextInput::make('notes')
          ->columnSpan(3)
          ->label('ملاحظات'),
        Hidden::make('imp_exp')
          ->default(0),
        Hidden::make('user_id')
          ->default(Auth::id())
      ])->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
       ->defaultSort('id','desc')
      ->columns([
        TextColumn::make('id')
          ->searchable()
          ->label('الرقم الألي'),
        TextColumn::make('receipt_date')
          ->searchable()
          ->label('التاريخ'),
        TextColumn::make('supplier.name')
          ->searchable()
          ->label('اسم المورد'),
        TextColumn::make('price_type.name')
             ->color(function (Recsupp $record) {
                        if ($record->price_type_id!=1) return 'info';
                    })
                    ->weight(function (Recsupp $record) {
                        if ($record->price_type_id!=1) return FontWeight::Bold;
                    })
                    ->description(function (Recsupp $record){
                        $name=null;
                        if ($record->acc_id) {$name=Acc::find($record->acc_id)->name;}
                        if ($record->kazena_id) {$name=Kazena::find($record->kazena_id)->name;}
                        return $name;
                    })
          ->label('طريقة الدفع'),
        TextColumn::make('rec_who')
          ->label('البيان')
          ->badge(),
          TextColumn::make('Place.name')
              ->label('المكان')
              ->searchable()
              ->sortable(),
        TextColumn::make('val')
          ->searchable()
          ->label('المبلغ'),

        TextColumn::make('notes')
          ->label('ملاحظات'),
      ])
      ->filters([
        SelectFilter::make('supplier_id')
          ->options(Supplier::all()->pluck('name', 'id'))
          ->searchable()
          ->label('مورد معين'),
          SelectFilter::make('place_id')
              ->options(Place::all()->pluck('name', 'id'))
              ->searchable()
              ->label('مكان معين'),
        Filter::make('is_order')
          ->label('ايصالات فاتورة')
          ->query(fn (Builder $query): Builder => $query->whereIn('rec_who', [3,4])),
        Filter::make('is_imp')
          ->label('ايصالات قبض')
          ->query(fn (Builder $query): Builder => $query->where('rec_who', 1)),
        Filter::make('is_exp')
          ->label('ايصالات دقع')
          ->query(fn (Builder $query): Builder => $query->where('rec_who', 2)),
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
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['Date2'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
      ])
      ->recordActions([
        EditAction::make()->iconButton()
            ->visible(fn(Recsupp $record): bool =>
                $record->rec_who->value<7
                || Auth::user()->can('الغاء ايصالات موردين')
            ),
        DeleteAction::make()->iconButton()
            ->visible(fn(Recsupp $record): bool =>
            $record->rec_who->value<7
            || Auth::user()->can('الغاء ايصالات موردين')
        )
          ->modalHeading('حذف الإيصال')
          ->after(function (Recsupp $record) {
              if ($record->rec_who->value==3 || $record->rec_who->value==4 || $record->rec_who->value==5 || $record->rec_who->value==6) {
                $sum=Recsupp::where('buy_id',$record->buy_id)->whereIn('rec_who',[3,6])->sum('val');
                $sub=Recsupp::where('buy_id',$record->buy_id)->whereIn('rec_who',[4,5])->sum('val');
              $buy=Buy::find($record->buy_id);
              $buy->pay=$sub-$sum;
              $buy->baky=$buy->tot-$sub+$sum;
              $buy->save();

            }

          }),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }

    public static function getPages(): array
    {
        return [
            'index' => ListRecsupps::route('/'),
            'create' => CreateRecsupp::route('/create'),
            'edit' => EditRecsupp::route('/{record}/edit'),
        ];
    }
}
