<?php

namespace App\Filament\Resources;

use App\Enums\RecWho;
use App\Filament\Resources\ReceiptResource\Pages;

use App\Models\Receipt;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
    protected static ?string $navigationLabel = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 3;

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
                  ->selectRaw('\' إجمالي الفاتورة \'+str(tot)+\' بتاريخ \'+convert(varchar,order_date) as name,id ')
                  ->pluck('name', 'id'))
                ->searchable()
                ->requiredIf('rec_who',3,)
                ->visible(fn(Get $get): bool =>$get('rec_who')==3)
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
                TextColumn::make('customer.name')
                    ->label('اسم الزبون'),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
