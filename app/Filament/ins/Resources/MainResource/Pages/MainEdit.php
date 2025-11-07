<?php

namespace App\Filament\ins\Resources\MainResource\Pages;

use App\Filament\ins\Resources\MainResource;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Sell;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MainEdit extends Page
{

    use InteractsWithRecord;
    protected static string $resource = MainResource::class;
    protected string $view = 'filament.ins.resources.main-resource.pages.main-edit';
    protected ?string $heading="";

    public $main_id;
    public $sell_id;
    public $main_id_edited;

    public $main;
    public $contData;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->main_id=$this->record->id;
        $this->sell_id=$this->record->sell_id;
        $this->main_id_edited=$this->main_id;
        $this->main=Main::with('Sell')->find($this->main_id);
        $arr=$this->main->toArray();

       // Arr::add($arr,'total',$this->main->Sell->total);

        $this->contForm->fill($arr);
    }
    public function go($who){
        $this->dispatch('gotoitem', test: $who);
    }
    public function store(){

        $this->validate();
      //  info($this->contData);
        Main::find($this->main_id)->update(collect($this->contData)->except(['total','pay','baky','sell','customer','name'])->toArray());
        Notification::make()
            ->title('تم تحزين البانات بنجاح')
            ->success()
            ->send();
        redirect()->to(MainResource::getUrl('index'));
    }
    protected function contForm(Schema $schema): Schema
    {
        return $schema
            ->model(Main::class)
            ->statePath('contData')
         ->components([
            Section::make()
                ->schema([
                    Select::make('sell_id')
                        ->label('فاتورة المبيعات')
                        ->relationship('Sell','name',
                            modifyQueryUsing: fn (Builder $query) => $query->WhereDoesntHave('Main')->orWhere('id',$this->sell_id),)
                        ->getOptionLabelFromRecordUsing(fn (Model $record) =>
                            "{$record->id} {$record->Customer->name} {$record->total}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state,Set $set){
                            $this->Sell=Sell::find($state);
                            $set('sell.total',$this->Sell->total);
                            $set('sell.pay',$this->Sell->pay);
                            $set('sell.baky',$this->Sell->baky);
                            $set('sul',$this->Sell->baky);
                            $set('id',Main::Max('id')+1);
                            $set('customer_id',$this->Sell->customer_id);
                            $set('name',$this->Sell->Customer->name);
                            $this->go('main_id');
                        }),
                    TextInput::make('name')
                     ->label('الزبون')
                     ->columnSpan(3)
                     ->disabled(),
                    Hidden::make('customer_id'),
                    TextInput::make('sell.total')
                        ->label('الاجمالي')
                        ->disabled(),
                    TextInput::make('sell.pay')
                        ->label('المدفوع')
                        ->disabled(),
                    TextInput::make('sell.baky')
                        ->label('الباقي')
                        ->disabled(),

                ])
                ->columns(5),
            Section::make()
                ->schema([
                    TextInput::make('id')
                        ->label('رقم العقد')
                        ->required()
                        ->unique(ignorable: $this->record)
                        ->unique(table: Main_arc::class)
                        ->numeric()
                        ->extraAttributes([
                            'wire:keydown.enter'=>'$dispatch("gotoitem", {test: "acc"})',

                        ])
                        ->id('main_id'),
                    Select::make('bank_id')
                        ->label('المصرف')
                        ->columnSpan(2)
                        ->relationship('Bank','BankName')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Section::make('ادخال مصارف')
                                ->description('ادخال بيانات مصرف .. ويمكن ادخال المصرف التجميعي اذا كان غير موجود بالقائمة')
                                ->schema([
                                    TextInput::make('BankName')
                                        ->required()
                                        ->label('اسم المصرف')
                                        ->maxLength(255),
                                    Select::make('taj_id')
                                        ->relationship('Taj','TajName')
                                        ->label('المصرف التجميعي')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('TajName')
                                                ->required()
                                                ->label('المصرف التجميعي')
                                                ->maxLength(255),
                                            TextInput::make('TajAcc')
                                                ->label('رقم الحساب')
                                                ->required(),
                                        ])
                                        ->required(),
                                ])
                        ])
                        ->editOptionForm([
                            Section::make('ادخال مصارف')
                                ->description('ادخال بيانات مصرف .. ويمكن ادخال المصرف التجميعي اذا كان غير موجود بالقائمة')
                                ->schema([
                                    TextInput::make('BankName')
                                        ->required()
                                        ->label('اسم المصرف')
                                        ->maxLength(255),

                                ])
                        ])
                        ->createOptionAction(fn ($action) => $action->color('success'))
                        ->editOptionAction(fn ($action) => $action->color('info'))
                        ->afterStateUpdated(function (){
                            $this->go('acc');
                        })
                        ->id('bank_id')
                        ->required(),
                    TextInput::make('acc')
                        ->label('رقم الحساب')
                        ->required()
                        ->id('acc')
                        ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "sul_begin"})',]),
                    DatePicker::make('sul_begin')
                        ->required()
                        ->label('تاريخ العقد')
                        ->maxDate(now())
                        ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "kst_count"})',])
                        ->id('sul_begin'),
                    TextInput::make('sul')
                        ->label('قيمة العقد')
                        ->readOnly()
                        ->live(onBlur: true)
                        ->readOnly()                     ,
                    TextInput::make('kst_count')
                        ->label('عدد الأقساط')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get,Set $set) {
                            $val=$get('sul') / $get('kst_count');
                            $set('kst', $val);
                        })
                        ->required()
                        ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "kst"})',])
                        ->id('kst_count'),
                    TextInput::make('kst')
                        ->label('القسط')
                        ->id('kst')
                        ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "notes"})',])
                        ->required(),
                    TextInput::make('notes')
                        ->label('ملاحظات')
                        ->extraAttributes(['x-on:keyup.enter'=>"\$wire.store",])
                        ->id('notes')
                        ->columnSpanFull(),
                    \Filament\Schemas\Components\Actions::make([
                        Action::make('store')
                            ->label('تخزين')
                            ->color('success')
                            ->action(function (){
                                $this->store();
                            }),
                        Action::make('cancel')
                            ->label('تجاهل')
                            ->color('info')
                            ->action(function (){
                                $this->mount();
                            }),

                    ])

                ])
                ->columns(4),

        ]);
    }
}
