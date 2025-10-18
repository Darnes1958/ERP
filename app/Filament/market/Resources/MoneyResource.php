<?php

namespace App\Filament\market\Resources;

use App\Enums\RecWhoMoney;
use App\Filament\market\Resources\MoneyResource\Pages\CreateMoney;
use App\Filament\market\Resources\MoneyResource\Pages\EditMoney;
use App\Filament\market\Resources\MoneyResource\Pages\ListMoney;
use App\Filament\Resources\MoneyResource\Pages;
use App\Filament\Resources\MoneyResource\RelationManagers;
use App\Models\Acc;
use App\Models\Kazena;
use App\Models\Money;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MoneyResource extends Resource
{
    protected static ?string $model = Money::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationLabel = 'تحويل';
  protected static string | \UnitEnum | null $navigationGroup = 'تحويلات بين الخزائن والمصارف';
  protected static ?int $navigationSort = 1;

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->can('ادخال تحويل');
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
                ->options(RecWhoMoney::class)
                ->columnSpanFull(),

              Section::make()
                ->schema([
                 Select::make('kazena_id')
                   ->label('من الخزينة')
                     ->options(Kazena::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Get $get){
                     return $get('rec_who')->value==1 || $get('rec_who')->value==2;
                   })
                   ->live()
                   ->visible(function (Get $get){
                     return $get('rec_who')->value==1 || $get('rec_who')->value==2;
                   })
                   ->preload(),
                 Select::make('acc_id')
                   ->label('من الحساب المصرفي')
                   ->options(Acc::all()->pluck('name','id'))
                   ->relationship('Acc','name')
                   ->searchable()
                   ->required(function (Get $get){
                     return $get('rec_who')->value==3 || $get('rec_who')->value==4;
                   })

                   ->visible(function (Get $get){
                     return $get('rec_who')->value==3 || $get('rec_who')->value==4;
                   })
                   ->preload(),
                 Select::make('kazena2_id')
                   ->label('إلي الحزينة')
                     ->options(Kazena::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Get $get){
                     return $get('rec_who')->value==1 || $get('rec_who')->value==3;
                   })
                   ->live()
                   ->visible(function (Get $get){
                     return $get('rec_who')->value==1 || $get('rec_who')->value==3;
                   })
                   ->preload(),
                 Select::make('acc2_id')
                   ->label('إلي الحساب المصرفي')
                     ->options(Acc::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Get $get){
                     return $get('rec_who')->value==2 || $get('rec_who')->value==4;
                   })
                   ->live()
                   ->visible(function (Get $get){
                     return $get('rec_who')->value==2 || $get('rec_who')->value==4;
                   })
                   ->preload(),
                 DatePicker::make('tran_date')
                   ->required()
                   ->default(now())
                   ->label('التاريخ'),
                 TextInput::make('amount')
                   ->required()
                   ->numeric()
                   ->label('المبلغ'),
                  Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('ملاحظات'),

               ])
                ->columns(2)
                ->columnSpan(2),

              Hidden::make('price_type_id')
               ->default(function (Get $get) {
                 if ($get('rec_who')->value==4) return 2; else return  1;
               }),
              Hidden::make('user_id')
                ->default(Auth::id()),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
          ->defaultSort('created_at','desc')
            ->columns([
                TextColumn::make('rec_who')
                ->sortable()
                ->label('البيان')
                ->badge(),
                TextColumn::make('tran_date')
                  ->sortable()
                  ->label('التاريخ'),
                TextColumn::make('from')
                  ->state(function (Money $record): string {
                    if ($record->rec_who->value==1 || $record->rec_who->value==2)
                    return Kazena::find($record->kazena_id)->name;
                    else
                      return Acc::find($record->acc_id)->name;

                  })
                  ->label('من'),
                TextColumn::make('to')
                  ->state(function (Money $record): string {
                    if ($record->rec_who->value==1 || $record->rec_who->value==3)
                      return Kazena::find($record->kazena2_id)->name;
                    else
                      return Acc::find($record->acc2_id)->name;

                  })
                  ->label('إلي'),
              TextColumn::make('amount')
                ->label('المبلغ'),
              TextColumn::make('notes')
                ->label('ملاحظات'),
            ])
            ->filters([
              SelectFilter::make('rec_who')
                ->options(RecWhoMoney::class)
                ->searchable()
                ->label('البيان'),
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
                      fn (Builder $query, $date): Builder => $query->whereDate('tran_date', '>=', $data['Date1']),
                    )
                    ->when(
                      $data['Date2'],
                      fn (Builder $query, $date): Builder => $query->whereDate('tran_date', '<=', $data['Date2']),
                    );
                })
            ])
            ->recordActions([
                EditAction::make()
                ->iconButton(),
              DeleteAction::make()->visible(Auth::user()->can('الغاء تحويل'))
                ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMoney::route('/'),
            'create' => CreateMoney::route('/create'),
            'edit' => EditMoney::route('/{record}/edit'),
        ];
    }
}
