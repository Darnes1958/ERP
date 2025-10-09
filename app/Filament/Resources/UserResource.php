<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\OurCompany;
use App\Models\Place;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
  protected static string | \UnitEnum | null $navigationGroup='اعدادات';
  protected static ?string $navigationLabel='مستخدمين وصلاحيات';
  protected static ?string $pluralLabel='مستخدم';

  public static function shouldRegisterNavigation(): bool
  {
      return Auth::user()->hasRole('admin');
  }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('الاسم')->unique(ignoreRecord: true)->required(),
                TextInput::make('email')->label('الايميل')->email()->unique(ignoreRecord: true)->required(),
                TextInput::make('password')->required()->visibleOn('create'),
                Select::make('place_id')
                 ->options(Place::all()->pluck('name', 'id'))
                ->searchable()
                ->preload()

                ->label('مكان العمل (صالة او المعرض)'),
                Select::make('company')
                  ->label('Company')
                  ->visible(Auth::id()==1)
                  ->options(OurCompany::all()->pluck('Company', 'Company')->toArray()),
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
                Radio::make('status')
                 ->options([
                     1=>'نشط',
                     0=>'غير نشط',

                 ])
                    ->inline()
                    ->inlineLabel(false)
                    ->visibleOn('edit')
                ->label('الحالة')

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
                IconColumn::make('status')

                   ->label('الحالة'),
                TextColumn::make('Place.name')->label('المكان'),
                TextColumn::make('company')->visibleOn(Auth::id()==1),
                TextColumn::make('created_at')->label('تاريخ الادخال'),
                TextColumn::make('updated_at')->label('تاريخ التعديل'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
