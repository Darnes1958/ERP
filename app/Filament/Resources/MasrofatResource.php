<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasrofatResource\Pages;
use App\Filament\Resources\MasrofatResource\RelationManagers;
use App\Models\Item;
use App\Models\Masr_type;
use App\Models\Masrofat;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MasrofatResource extends Resource
{
    protected static ?string $model = Masrofat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralLabel='مصروفات';
    protected static ?string $navigationGroup='مصروفات';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصروفات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Select::make('masr_type_id')
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
                Forms\Components\Radio::make('pay_type')
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
                    ->visible(function (Forms\Get $get){
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
                    ->visible(function (Forms\Get $get){
                        return $get('pay_type')==1;
                    }),
                Forms\Components\DatePicker::make('masr_date')
                 ->required()
                 ->default(now())
                ->label('التاريخ'),
                TextInput::make('val')
                 ->numeric()
                 ->required()
                 ->label('المبلغ'),
              TextInput::make('notes')
                ->label('ملاحظات'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('masr_date','desc')
            ->columns([
                Tables\Columns\TextColumn::make('masr_date')
                 ->label('التاريخ')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('Masr_type.name')
                    ->label('البيان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Acc.name')
                    ->label('المصرف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Kazena.name')
                    ->label('الخزينة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('val')
                    ->label('المبلغ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
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
                                fn (Builder $query, $date): Builder => $query->whereDate('masr_date', '>=', $date),
                            )
                            ->when(
                                $data['Date2'],
                                fn (Builder $query, $date): Builder => $query->whereDate('masr_date', '<=', $date),
                            );
                    })
            ], layout: Tables\Enums\FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::ExtraSmall)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->visible(Auth::user()->can('الغاء مصروفات')),
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
            'index' => Pages\ListMasrofats::route('/'),
            'create' => Pages\CreateMasrofat::route('/create'),
            'edit' => Pages\EditMasrofat::route('/{record}/edit'),
        ];
    }
}
