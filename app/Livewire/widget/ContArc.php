<?php

namespace App\Livewire\widget;

use App\Models\aksat\MainArc;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Overkst;
use App\Models\OverTar\over_kst_a;
use App\Models\OverTar\tar_kst;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class ContArc extends BaseWidget
{
    public  $cust;

    protected static ?string $heading="";

    #[On('Take_Main_Id')]
    public function ContCust($main_id){

        $this->cust=Main::find($main_id)->customer_id;

    }
    public function mount($main_id){
        $this->cust=Main::find($main_id)->customer_id;
    }

    public function Do($main_id){

        $this->dispatch('open-modal', id: 'mymainModal',main_id: $main_id);
        $this->dispatch('showMainArcModal', main_id: $main_id);

    }
    public function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('أرشيف')
            ->paginated(false)
            ->defaultSort('sul_begin')
            ->query(function (){
                $main=Main_arc::where('customer_id',$this->cust);
                return $main;
            })

            ->recordUrl(null)
            ->columns([
                TextColumn::make('id')
                    ->action(function (Main_arc $record){$this->Do($record->id);})
                    ->tooltip('انقر للعرض')
                    ->size(TextSize::ExtraSmall)
                    ->label(new HtmlString('<span style="font-size: smaller;color: #00bb00">عقود سابقة&nbsp;&nbsp;</span>')),
                TextColumn::make('sul_begin')
                    ->action(function (Main_arc $record){$this->Do($record->id);})
                    ->tooltip('انقر للعرض')
                    ->size(TextSize::ExtraSmall)
                    ->label('التاريخ'),

                TextColumn::make('sul')
                    ->size(TextSize::ExtraSmall)
                    ->tooltip('انقر للعرض')
                    ->action(function (Main_arc $record){$this->Do($record->id);})
                    ->label('الاجمالي'),
                TextColumn::make('kst')
                    ->size(TextSize::ExtraSmall)
                    ->tooltip('انقر للعرض')
                    ->action(function (Main_arc $record){$this->Do($record->id);})
                    ->label('القسط'),
            ]);
    }
}
