<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\OurCompany;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
  protected static ?string $navigationGroup='اعدادات';
  protected static ?string $navigationLabel='مستخدمين وصلاحيات';
  protected static ?string $pluralLabel='مستخدم';

  public static function shouldRegisterNavigation(): bool
  {
      return Auth::user()->hasRole('Admin');
  }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('الاسم')->unique(ignoreRecord: true)->required(),
                TextInput::make('email')->label('الايميل')->email()->unique(ignoreRecord: true)->required(),
                TextInput::make('password')->required()->visibleOn('create'),
                Select::make('roles')
                    ->label('صلاحيات مجمعة')
                    ->searchable()
                    ->multiple()
                    ->relationship('roles', 'name', fn (Builder $query) => $query
                        ->when(Auth::id()!=1,function ($q) {$q->where('name','!=','Admin');})
                        ->where('for_who','sell')
                    )

                    ->preload(),
              Select::make('permissions')
                  ->label('صلاحيات مفردة')
                ->searchable()
                ->multiple()
                ->relationship('permissions','name', fn (Builder $query) => $query
                    ->where('for_who','sell'))
                ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->id() != 1) {
                    return $query
                        ->where('company', auth()->user()->company)
                        ->where('id','!=',1)
                        ;
                }
            })
            ->columns([
                TextColumn::make('id')->label('الرقم الألي'),
                TextColumn::make('name')->label('الاسم'),
                TextColumn::make('email')->label('الايميل'),
                TextColumn::make('company')->visibleOn(Auth::id()==1),
                TextColumn::make('created_at')->label('تاريخ الادخال'),
                TextColumn::make('updated_at')->label('تاريخ التعديل'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
