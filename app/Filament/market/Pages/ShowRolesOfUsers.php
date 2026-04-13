<?php

namespace App\Filament\Market\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ShowRolesOfUsers extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.market.pages.show-roles-of-users';
    protected ?string $heading='عرض الصلاحيات المجمعة للمستخدمين';
    protected static ?string $navigationLabel='عرض الصلاحيات المجمعة للمستخدمين';
    protected static string | \UnitEnum | null $navigationGroup='ادارة';
    protected static ?string $navigationParentItem='مستخدمين وصلاحيات';
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }
    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {

                return Role::query()
                    ->when('for_who'!=null,function ($q){$q->where('for_who','sell');})
                    ;

            })
            ->recordUrl(false)
            ->columns([
                TextColumn::make('name')->searchable()->label('الصلاحية المجمعة'),
                TextColumn::make('Permissions.name')
                    ->label('الصلاحيات المفردة')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
                TextColumn::make('users.name')
                    ->label('المستخدم')
                    ->getStateUsing(fn ($record) =>  User::where('company',Auth::user()->company)
                        ->role($record->name)->get()->pluck('name')),
            ])
            ;
    }
}
