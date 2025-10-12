<?php

namespace App\Filament\ins\Resources\MainResource\Pages;

use App\Filament\ins\Resources\MainResource;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Sell;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MainCreate extends Page  implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = MainResource::class;
    protected string $view = 'filament.ins.resources.main-resource.pages.main-create';
    protected ?string $heading="";


    public $contData;


    public $Sell;
    public function mount(): void
    {
        $this->contForm->fill(['sul_begin'=>now(),'id'=>Main::max('id')+1]);
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(),[
           'contForm'=> $this->makeForm()
            ->model(Main::class)
            ->components($this->getContFormSchema())
            ->statePath('contData'),
        ]);
    }
public function go($who){
    $this->dispatch('gotoitem', test: $who);
}
public function store(){
  $this->validate();
  Main::create(collect($this->contData)->except(['total','pay','baky'])->toArray());
  Notification::make()
    ->title('تم تحزين البيانات بنجاح')
    ->success()
    ->send();
  $this->mount();
}
    protected function getContFormSchema(): array
    {
        return [
                Section::make()
                 ->schema([
                   Select::make('sell_id')
                     ->label('فاتورة المبيعات')
                     ->relationship('Sell','name',modifyQueryUsing: fn (Builder $query) =>
                        $query->WhereDoesntHave('Main')->where('price_type_id','=',3),)
                     ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->id} {$record->Customer->name} {$record->total}")
                     ->searchable()
                     ->preload()
                     ->live()
                     ->required()
                     ->columnSpan(2)
                     ->afterStateUpdated(function ($state,Set $set){
                         $this->Sell=Sell::find($state);
                         $set('total',$this->Sell->total);
                         $set('pay',$this->Sell->pay);
                         $set('baky',$this->Sell->baky);
                         $set('sul',$this->Sell->baky);
                         $set('id',Main::Max('id')+1);
                         $set('customer_id',$this->Sell->customer_id);
                         $this->go('main_id');
                       }),
                   Hidden::make('customer_id'),
                   TextInput::make('total')
                    ->label('الاجمالي')
                    ->disabled(),
                   TextInput::make('pay')
                     ->label('المدفوع')
                     ->disabled(),
                   TextInput::make('baky')
                     ->label('الباقي')
                     ->disabled(),

                 ])
                 ->columns(5),
                Section::make()
                 ->schema([
                   TextInput::make('id')
                     ->label('رقم العقد')
                     ->required()
                     ->unique(ignoreRecord: true)
                     ->unique(table: Main_arc::class)
                     ->default(Main::max('id')+1)
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
                     ->afterStateUpdated(function ($state,Set $set){
                         $set('taj_id',Bank::find($state)->taj_id);
                         $this->go('acc');

                     })
                     ->id('bank_id')
                     ->required(),

                   Hidden::make('taj_id'),

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
                     Actions::make([
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

        ];
    }
}
