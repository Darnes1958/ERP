<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationLabel='زبائن';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup='زبائن وموردين';

    public static function form(Form $form): Form
    {
        return $form
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

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('id')
                ->sortable()
                ->searchable()
                ->label('الرقم الألي'),
              TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->label('الاسم'),
              TextColumn::make('address')
                ->icon('heroicon-o-envelope')
                ->iconColor('blue')
                ->label('العنوان'),
              TextColumn::make('mdar')
                ->searchable()
                ->icon('heroicon-o-phone')
                ->iconColor('green')
                ->label('مدار'),
              TextColumn::make('libyana')
                ->searchable()
                ->icon('heroicon-o-phone')
                ->label('لبيانا')
                ->iconColor('Fuchsia'),
            ])
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                 ->iconButton()
                 ->hidden(fn(Customer $record)=>$record->id<3),
                Tables\Actions\DeleteAction::make()
                  ->iconButton()
                  ->modalHeading('حذف زبون')
                 ->modalDescription('هل انت متأكد من الغاء هذا الزبون ؟')
                 ->hidden(fn(Customer $record)=>
                   Sell::where('customer_id',$record->id)->exists()
                   || $record->id<3
                   || Receipt::where('customer_id',$record->id)->exists()),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}