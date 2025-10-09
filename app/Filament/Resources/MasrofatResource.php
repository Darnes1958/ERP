<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use App\Filament\Resources\MasrofatResource\Pages\ListMasrofats;
use App\Filament\Resources\MasrofatResource\Pages\CreateMasrofat;
use App\Filament\Resources\MasrofatResource\Pages\EditMasrofat;
use App\Filament\Resources\MasrofatResource\Pages;
use App\Filament\Resources\MasrofatResource\RelationManagers;
use App\Models\Item;
use App\Models\Masr_type;
use App\Models\Masrofat;
use App\Models\Place;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MasrofatResource extends Resource
{
    protected static $place_id;
    protected static ?string $model = Masrofat::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralLabel='مصروفات';
    protected static string | \UnitEnum | null $navigationGroup='مصروفات';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصروفات');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('masr_type_id')
                ->relationship('Masr_type','name')
                ->searchable()
                ->required()
                ->preload()
                   ->createOptionForm([
                       Section::make('ادخال نوع مصروفات جديد')
                           ->schema([
                               TextInput::make('name')
                                   ->label('البيان')
                                   ->required()
                                   ->autofocus()
                                   ->unique(ignoreRecord: true)
                                   ->validationMessages([
                                       'unique' => ' :attribute مخزون مسبقا ',
                                   ])        ,
                           ])
                   ])
                   ->editOptionForm([
                       Section::make('تعديل بيانات خزينة')
                           ->schema([
                               TextInput::make('name')
                                   ->label('البيان')
                                   ->required()
                                   ->autofocus()
                                   ->columnSpan(2)
                                   ->unique(ignoreRecord: true)
                                   ->validationMessages([
                                       'unique' => ' :attribute مخزون مسبقا ',
                                   ])        ,
                           ])
                   ])
                ->label('نوع المصروفات'),
                Radio::make('pay_type')
                    ->options([
                        0 => 'مصرفي',
                        1 => 'نقدا',
                    ])
                    ->default(1)
                    ->inline()
                    ->inlineLabel(false)
                    ->live()
                    ->label('طريقة الدفع')
                    ,
                Select::make('acc_id')
                    ->relationship('Acc','name')
                    ->label('المصرف')
                    ->preload()
                    ->requiredIf('pay_type', 0)
                    ->visible(function (Get $get){
                        return $get('pay_type')==0;
                    }),
                Select::make('kazena_id')
                    ->relationship('Kazena','name')
                    ->label('الخزينة')
                    ->preload()
                    ->requiredIf('pay_type', 1)
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

                                TextInput::make('raseed')
                                    ->label('رصيد بداية المدة')
                                    ->numeric()
                                    ->required()

                            ])->columns(2)
                    ])
                    ->visible(function (Get $get){
                        return $get('pay_type')==1;
                    }),
                Select::make('place_id')
                    ->relationship('Place','name')
                    ->label('المكان')
                    ->placeholder('غير محدد')
                    ->preload(),
                DatePicker::make('masr_date')
                 ->required()
                 ->default(now())
                ->label('التاريخ'),
                TextInput::make('val')
                 ->numeric()
                 ->required()
                 ->label('المبلغ'),
              TextInput::make('notes')
                ->label('ملاحظات'),
                Hidden::make('user_id')->default(Auth::id())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('masr_date','desc')
            ->columns([
                TextColumn::make('masr_date')
                 ->label('التاريخ')
                ->searchable()
                ->sortable(),
                TextColumn::make('Masr_type.name')
                    ->label('البيان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Acc.name')
                    ->label('المصرف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Kazena.name')
                    ->label('الخزينة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Place.name')
                    ->label('المكان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('val')
                    ->label('المبلغ')
                    ->searchable()
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable()
                    ->sortable(),


            ])
            ->filters([
                SelectFilter::make('place_id')
                    ->options(Place::all()->pluck('name', 'id'))
                    ->searchable()
                    ->label('مكان معين'),
                SelectFilter::make('Masr_type')
                    ->relationship('Masr_type','name')
                    ->searchable()
                    ->preload()
                    ->label('نوع المصروفات'),
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
                                fn (Builder $query, $date): Builder => $query->whereDate('masr_date', '>=', $date),
                            )
                            ->when(
                                $data['Date2'],
                                fn (Builder $query, $date): Builder => $query->whereDate('masr_date', '<=', $date),
                            );
                    }),

            ])
            ->filtersFormWidth(Width::ExtraSmall)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(Auth::user()->can('الغاء مصروفات')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('editPlace')
                        ->deselectRecordsAfterCompletion()
                        ->label('تعديل المكان')
                        ->form([
                            Select::make('place_id')
                                ->label('قم باختيار المكان')
                                ->options(Place::query()->pluck('name', 'id'))
                                ->preload()
                                ->searchable(),
                        ])
                        ->requiresConfirmation()
                        ->action(function (Collection $records,array $data) : void {
                            if ($data['place_id']!=null)
                                $records->each->update(['place_id'=>$data['place_id']]);
                        }),


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
            'index' => ListMasrofats::route('/'),
            'create' => CreateMasrofat::route('/create'),
            'edit' => EditMasrofat::route('/{record}/edit'),
        ];
    }
}
