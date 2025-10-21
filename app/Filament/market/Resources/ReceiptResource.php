<?php

namespace App\Filament\market\Resources;

use App\Enums\RecWho;
use App\Filament\market\Resources\ReceiptResource\Pages\CreateReceipt;
use App\Filament\market\Resources\ReceiptResource\Pages\EditReceipt;
use App\Filament\market\Resources\ReceiptResource\Pages\ListReceipts;

use App\Filament\Tables\SellTable;
use App\Models\Acc;
use App\Models\Customer;
use App\Models\Kazena;
use App\Models\Place;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'ايصالات زبائن';
    protected static string | \UnitEnum | null $navigationGroup = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال ايصالات زبائن');
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
               Select::make('customer_id')
                ->label('الزبون')
                ->relationship('Customer','name')
                ->searchable()
                ->required()
                ->live()
                ->preload()
                ->createOptionForm([
                       Section::make('ادخال زبون جديد')
                           ->schema([
                               TextInput::make('name')
                                   ->required()
                                   ->label('الاسم'),
                               Select::make('customer_type_id')
                                   ->label('التصنيف')
                                   ->relationship('Customer_type','name')
                                   ->required(),
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
                       Section::make('تعديل بيانات زبون')
                           ->schema([
                               TextInput::make('name')
                                   ->required()
                                   ->label('الاسم'),
                               Select::make('customer_type_id')
                                   ->label('التصنيف')
                                   ->relationship('Customer_type','name')
                                   ->required(),
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
              ModalTableSelect::make('sell_id')
                  ->label('رقم الفاتورة')
                  ->relationship('Sell','id')
                  ->selectAction(
                      fn (Action $action) => $action
                          ->label('إضغط هنا لبحث')
                          ->modalHeading('البحث عن فاتورة')
                          ->modalSubmitActionLabel('تأكيد الإختيار'),
                  )
                  ->tableConfiguration(SellTable::class)
                  ->getOptionLabelFromRecordUsing(fn (Sell $record): string => "{$record->id} ({$record->Customer->name}  {$record->total})")
                  ->tableArguments(function (Get $get): array {
                      return [
                          'customer_id' => $get('customer_id'),
                      ];
                  })
                  ->requiredIf('rec_who',[3,4])
                  ->visible(fn(Get $get): bool =>($get('rec_who')->value==3 || $get('rec_who')->value ==4)),
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
                        Section::make('تعديل بيانات مصرف')
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

                    ->disabled(function ($state) {return $res=Kazena::where('user_id',Auth::id())->first();})
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
                    ->requiredIf('rec_who',3)
                    ->live()
                    ->preload()
                    ->disabled(function () {return Auth::user()->place_id!=null;})
                    ->default(function (){
                        if (Auth::user()->place_id!=null) return Auth::user()->place_id;
                        else return null;
                    }),


                TextInput::make('notes')
                 ->columnSpan(3)
                 ->label('ملاحظات'),
                Hidden::make('sell_id'),

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
                TextColumn::make('index')
                    ->label('ت')
                    ->rowIndex(),
                TextColumn::make('id')
                  ->searchable()
                 ->label('الرقم الألي'),
                TextColumn::make('receipt_date')
                  ->searchable()
                    ->label('التاريخ'),
                TextColumn::make('customer.name')
                  ->searchable()
                    ->label('اسم الزبون'),
                TextColumn::make('price_type.name')
                    ->color(function (Receipt $record) {
                        if ($record->price_type_id!=1) return 'info';
                    })
                    ->weight(function (Receipt $record) {
                        if ($record->price_type_id!=1) return FontWeight::Bold;
                    })
                    ->description(function (Receipt $record){
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
              SelectFilter::make('customer_id')
                ->options(Customer::all()->pluck('name', 'id'))
                ->searchable()
                ->label('زبون معين'),
                SelectFilter::make('place_id')
                    ->options(Place::all()->pluck('name', 'id'))
                    ->searchable()
                    ->label('مكان معين'),
              Filter::make('is_sell')
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
                      fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '>=', $date),
                    )
                    ->when(
                      $data['Date2'],
                      fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '<=', $date),
                    );
                })
            ])
            ->recordActions([
              EditAction::make()->iconButton()
                  ->color('blue')
                  ->visible(fn(Receipt $record): bool =>
                      $record->rec_who->value<5
                      || !Auth::user()->can('االغاء ايصالات زبائن')),
              DeleteAction::make()->iconButton()
                  ->visible(fn(Receipt $record): bool =>
                      $record->rec_who->value<5
                       || !Auth::user()->can('االغاء ايصالات زبائن'))
                ->modalHeading('حذف الإيصال')
                ->after(function (Receipt $record) {
                  if ($record->rec_who->value==3 || $record->rec_who->value==4 || $record->rec_who->value==5 || $record->rec_who->value==6) {
                    $sum=Receipt::where('sell_id',$record->sell_id)->whereIn('rec_who',[3,6])->sum('val');
                    $sub=Receipt::where('sell_id',$record->sell_id)->whereIn('rec_who',[4,5])->sum('val');
                    $sell=Sell::find($record->sell_id);
                    $sell->pay=$sum-$sub;
                    $sell->baky=$sell->total-$sum+$sub;
                    $sell->save();
                  }
                }),
            ])
            ->toolbarActions([
               //
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'edit' => EditReceipt::route('/{record}/edit'),
        ];
    }
}
