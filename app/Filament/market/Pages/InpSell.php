<?php

namespace App\Filament\Market\Pages;

use App\Enums\PlaceType;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;

class InpSell extends Page implements HasSchemas,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    protected string $view = 'filament.market.pages.inp-sell';

    public $sellData,$tranData;

    public function sellForm(Schema $schema): Schema
    {
        return $schema
            ->model(Sell::class)
            ->statePath('sellData')
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->autofocus()
                            ->hiddenLabel()
                            ->prefix('التاريخ')
                            ->columnSpan(2)
                            ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                            ->required(),
                        Select::make('customer_id')
                            ->searchable()
                            ->preload()
                            ->hiddenLabel()
                            ->prefix('الزبون')
                            ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                            ->relationship('Customer','name')
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->createOptionForm([
                                Section::make('ادخال زبون جديد')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Select::make('customer_type_id')
                                            ->label('التصنيف')
                                            ->relationship('Customer_type','name')
                                            ->required(),
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
                                        Select::make('customer_type_id')
                                            ->label('التصنيف')
                                            ->relationship('Customer_type','name')
                                            ->required(),
                                        TextInput::make('address')
                                            ->label('العنوان'),
                                        TextInput::make('mdar')
                                            ->label('مدار'),
                                        TextInput::make('libyana')
                                            ->label('لبيانا'),
                                        Hidden::make('user_id')
                                            ->default(Auth::id()),

                                    ])->columns(2)
                            ])
                            ->id('customer_id'),
                        Select::make('place_id')
                            ->hiddenLabel()
                            ->prefix('نقطة البيع')
                            ->relationship('Place','name')
                            ->disabled(function (){
                                return Sell_tran_work::where('sell_id',$this->sell->id)->exists();
                            })
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                            ->createOptionForm([
                                Section::make('ادخال مكان تخزين')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('place_type')
                                            ->inline()
                                            ->options(PlaceType::class)
                                    ])
                            ])
                            ->editOptionForm([
                                Section::make('تعديل مكان تخزين')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('place_type')
                                            ->inline()
                                            ->options(PlaceType::class)
                                    ])->columns(2)
                            ])
                            ->id('place_id')
                            ->visible(Setting::find(Auth::user()->company)->many_place && Auth::user()->hasRole('admin')),
                        Select::make('price_type_id')
                            ->hiddenLabel()
                            ->prefix('طريقة الدفع')
                            ->columnSpan(2)
                            ->live()

                            ->relationship('Price_type','name')
                            ->required()
                            ->extraAttributes(['x-on:change' => "\$wire.updatePriceType"])
                            ->id('price_type_id'),
                        TextInput::make('tot')
                            ->hiddenLabel()
                            ->prefix('اجمالي الفاتورة')
                            ->columnSpan(2)
                            ->readOnly(),
                        TextInput::make('rate')
                            ->hiddenLabel()
                            ->prefix('النسبة')
                            ->prefixIcon('heroicon-m-chart-pie')
                            ->prefixIconColor('danger')
                            ->extraAttributes(['x-on:change' => "\$wire.updateDiffer"])
                            ->visible(fn()=>$this->sell->price_type_id==2)
                            ->columnSpan(2)
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('differ')
                            ->hiddenLabel()
                            ->prefix('فرق عملة')
                            ->prefixIcon('heroicon-m-document-plus')
                            ->prefixIconColor('success')
                            ->visible(fn()=>$this->sell->price_type_id==2)
                            ->columnSpan(2)
                            ->readOnly(),
                        TextInput::make('cost')
                            ->hiddenLabel()
                            ->prefix('تكاليف إضافية')
                            ->extraAttributes(['x-on:change' => "\$wire.updatePay"])
                            ->columnSpan(2)
                            ->numeric()
                            ->gte(0),
                        TextInput::make('pay')
                            ->hiddenLabel()
                            ->prefix('المدفوع')
                            ->columnSpan(2)
                            ->extraAttributes(['x-on:change' => "\$wire.updatePay"])
                            ->live(onBlur: true)
                            ->default('0')
                            ->id('pay'),

                        TextInput::make('baky')
                            ->hiddenLabel()
                            ->prefix('المتبقي')
                            ->columnSpan(2)
                            ->readOnly()
                            ->default('0'),
                        TextInput::make('total')
                            ->hiddenLabel()
                            ->prefix('الإجمالي النهائي')
                            ->columnSpan(2)
                            ->readOnly()
                            ->default('0'),

                        TextInput::make('notes')
                            ->hiddenLabel()
                            ->prefix('ملاحظات')
                            ->afterStateUpdated(function ($state){
                                $this->sell->notes=$state;
                                $this->sell->save();
                                Notification::make()
                                    ->title('تم تحزين البيانات بنجاح')
                                    ->success()
                                    ->send();
                            })
                            ->columnSpanFull(),

                    ])
                    ->columns(10)
            ]);;
    }
    public function tranForm(Schema $schema): Schema
    {
        return $schema
            ->model(Sell_tran::class)
            ->components([

            ]);
    }

}
