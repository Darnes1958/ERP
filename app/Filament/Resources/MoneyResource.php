<?php

namespace App\Filament\Resources;

use App\Enums\RecWho;
use App\Enums\RecWhoMoney;
use App\Filament\Resources\MoneyResource\Pages;
use App\Filament\Resources\MoneyResource\RelationManagers;
use App\Models\Acc;
use App\Models\Customer;
use App\Models\Kazena;
use App\Models\Money;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MoneyResource extends Resource
{
    protected static ?string $model = Money::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationLabel = 'تحويل';
  protected static ?string $navigationGroup = 'تحويلات بين الخزائن والمصارف';
  protected static ?int $navigationSort = 1;

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->can('ادخال تحويل');
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
                ->options(RecWhoMoney::class)
                ->columnSpanFull(),

              Forms\Components\Section::make()
                ->schema([
                 Select::make('kazena_id')
                   ->label('من الخزينة')
                     ->options(Kazena::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Forms\Get $get){
                     return $get('rec_who')==1 || $get('rec_who')==2;
                   })
                   ->live()
                   ->visible(function (Forms\Get $get){
                     return $get('rec_who')==1 || $get('rec_who')==2;
                   })
                   ->preload(),
                 Select::make('acc_id')
                   ->label('من الحساب المصرفي')
                   ->options(Acc::all()->pluck('name','id'))
                   ->relationship('Acc','name')
                   ->searchable()
                   ->required(function (Forms\Get $get){
                     return $get('rec_who')==3 || $get('rec_who')==4;
                   })

                   ->visible(function (Forms\Get $get){
                     return $get('rec_who')==3 || $get('rec_who')==4;
                   })
                   ->preload(),
                 Select::make('kazena2_id')
                   ->label('إلي الحزينة')
                     ->options(Kazena::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Forms\Get $get){
                     return $get('rec_who')==1 || $get('rec_who')==3;
                   })
                   ->live()
                   ->visible(function (Forms\Get $get){
                     return $get('rec_who')==1 || $get('rec_who')==3;
                   })
                   ->preload(),
                 Select::make('acc2_id')
                   ->label('إلي الحساب المصرفي')
                     ->options(Acc::all()->pluck('name','id'))
                   ->searchable()
                   ->required(function (Forms\Get $get){
                     return $get('rec_who')==2 || $get('rec_who')==4;
                   })
                   ->live()
                   ->visible(function (Forms\Get $get){
                     return $get('rec_who')==2 || $get('rec_who')==4;
                   })
                   ->preload(),
                 Forms\Components\DatePicker::make('tran_date')
                   ->required()
                   ->default(now())
                   ->label('التاريخ'),
                 Forms\Components\TextInput::make('amount')
                   ->required()
                   ->numeric()
                   ->label('المبلغ'),
                  Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('ملاحظات'),

               ])
                ->columns(2)
                ->columnSpan(2),

              Hidden::make('price_type_id')
               ->default(function (Forms\Get $get) {
                 if ($get('rec_who')==4) return 2; else return  1;
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
                Tables\Columns\TextColumn::make('rec_who')
                ->sortable()
                ->label('البيان')
                ->badge(),
                Tables\Columns\TextColumn::make('tran_date')
                  ->sortable()
                  ->label('التاريخ'),
                Tables\Columns\TextColumn::make('from')
                  ->state(function (Money $record): string {
                    if ($record->rec_who->value==1 || $record->rec_who->value==2)
                    return Kazena::find($record->kazena_id)->name;
                    else
                      return Acc::find($record->acc_id)->name;

                  })
                  ->label('من'),
                Tables\Columns\TextColumn::make('to')
                  ->state(function (Money $record): string {
                    if ($record->rec_who->value==1 || $record->rec_who->value==3)
                      return Kazena::find($record->kazena2_id)->name;
                    else
                      return Acc::find($record->acc2_id)->name;

                  })
                  ->label('إلي'),
              Tables\Columns\TextColumn::make('amount')
                ->label('المبلغ'),
              Tables\Columns\TextColumn::make('notes')
                ->label('ملاحظات'),
            ])
            ->filters([
              SelectFilter::make('rec_who')
                ->options(RecWhoMoney::class)
                ->searchable()
                ->label('البيان'),
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
                      fn (Builder $query, $date): Builder => $query->whereDate('tran_date', '>=', $data['Date1']),
                    )
                    ->when(
                      $data['Date2'],
                      fn (Builder $query, $date): Builder => $query->whereDate('tran_date', '<=', $data['Date2']),
                    );
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->iconButton(),
              Tables\Actions\DeleteAction::make()->visible(Auth::user()->can('الغاء تحويل'))
                ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMoney::route('/'),
            'create' => Pages\CreateMoney::route('/create'),
            'edit' => Pages\EditMoney::route('/{record}/edit'),
        ];
    }
}
