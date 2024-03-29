<?php

namespace App\Filament\Resources;

use App\Enums\RecWho;
use App\Filament\Resources\ReceiptResource\Pages;

use App\Models\Acc;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Sell;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'ايصالات زبائن';
    protected static ?string $navigationGroup = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('زبائن');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
              Select::make('sell_id')
                ->label('رقم الفاتورة')
                ->options(fn (Get $get): Collection => Sell::query()
                  ->where('customer_id', $get('customer_id'))
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
            ->columns([
                TextColumn::make('index')
                    ->label('ت')
                    ->rowIndex(),
                TextColumn::make('id')
                 ->label('الرقم الألي'),
                TextColumn::make('receipt_date')
                    ->label('التاريخ'),
                TextColumn::make('customer.name')
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
                        return $name;
                    })
                    ->label('طريقة الدفع'),
                TextColumn::make('rec_who')
                    ->label('البيان')

                    ->badge(),
                TextColumn::make('val')
                    ->label('المبلغ'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
              SelectFilter::make('customer_id')
                ->options(Customer::all()->pluck('name', 'id'))
                ->searchable()
                ->label('زبون معين'),
              Tables\Filters\Filter::make('is_sell')
                ->label('ايصالات فاتورة')

                ->query(fn (Builder $query): Builder => $query->whereIn('rec_who', [3,4])),
              Tables\Filters\Filter::make('is_imp')
                ->label('ايصالات قبض')
                ->query(fn (Builder $query): Builder => $query->where('rec_who', 1)),
              Tables\Filters\Filter::make('is_exp')
                ->label('ايصالات دقع')
                ->query(fn (Builder $query): Builder => $query->where('rec_who', 2)),
              Tables\Filters\Filter::make('created_at')
                ->form([
                  Forms\Components\DatePicker::make('Date1')
                   ->label('من تاريخ'),
                  Forms\Components\DatePicker::make('Date2')
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
            ->actions([
              Tables\Actions\EditAction::make()->iconButton()->color('blue')->visible(fn(Receipt $record) =>$record->rec_who->value<5),
              Tables\Actions\DeleteAction::make()->iconButton()
                  ->visible(fn(Receipt $record) =>$record->rec_who->value<5)
                ->modalHeading('حذف الإيصال')
                ->after(function (Receipt $record) {
                  if ($record->rec_who==3 || $record->rec_who==4) {

                    $sum=Receipt::where('sell_id',$record->sell_id)->whereIn('rec_who',[3,6])->sum('val');
                    $sub=Receipt::where('sell_id',$record->sell_id)->whereIn('rec_who',[4,5])->sum('val');
                    $sell=Sell::find($record->sell_id);
                    $sell->pay=$sum-$sub;
                    $sell->baky=$sell->tot-$sum+$sub;
                    $sell->save();

                  }

                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }
}
