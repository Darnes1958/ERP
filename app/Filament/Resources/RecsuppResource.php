<?php

namespace App\Filament\Resources;

use App\Enums\RecWho;
use App\Filament\Resources\RecsuppResource\Pages;
use App\Filament\Resources\RecsuppResource\RelationManagers;
use App\Models\Buy;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Sell;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecsuppResource extends Resource
{
    protected static ?string $model = Recsupp::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationLabel = 'ايصالات موردين';
  protected static ?string $navigationGroup = 'ايصالات قبض ودفع';
  protected static ?int $navigationSort = 2;

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
        TextColumn::make('id')
          ->label('الرقم الألي'),
        TextColumn::make('receipt_date')
          ->label('التاريخ'),
        TextColumn::make('supplier.name')
          ->label('اسم المورد'),
        TextColumn::make('price_type.name')
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
        SelectFilter::make('supplier_id')
          ->options(Supplier::all()->pluck('name', 'id'))
          ->searchable()
          ->label('مورد معين'),
        Tables\Filters\Filter::make('is_order')
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
        Tables\Actions\EditAction::make()->iconButton(),
        Tables\Actions\DeleteAction::make()->iconButton()
          ->modalHeading('حذف الإيصال')
          ->after(function (Recsupp $record) {
            if ($record->rec_who==3 || $record->rec_who==4) {
              $val=$record->val;
              $sum=Recsupp::where('buy_id',$record->buy_id)->sum('val');
              if ($record->rec_who == 3) $val=$sum-$val;
              if ($record->rec_who == 4) $val+=$sum;
              $buy=Buy::find($record->buy_id);
              $buy->pay=$val;
              $buy->baky=$buy->tot-$buy->pay;
              $buy->save();

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
            'index' => Pages\ListRecsupps::route('/'),
            'create' => Pages\CreateRecsupp::route('/create'),
            'edit' => Pages\EditRecsupp::route('/{record}/edit'),
        ];
    }
}