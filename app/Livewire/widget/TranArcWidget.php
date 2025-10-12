<?php

namespace App\Livewire\widget;

use App\Enums\Tar_type;
use App\Livewire\Traits\AksatTrait;
use App\Livewire\Traits\MainTrait;
use App\Models\Main;
use App\Models\Tran;
use App\Models\Trans_arc;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class TranArcWidget extends BaseWidget
{

    protected static ?string $heading='';
    public $main_id;
    #[On('Take_Main_Arc_Id')]
    public function do($main_id)
    {
        $this->main_id=$main_id;
    }

    public function mount($main_id){
        $this->main_id=$main_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('لا توجد أقساط مخصومة')
            ->emptyStateDescription('لم يتم خصم أقساط بعد')
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([5,12,15,50,'all'])
            ->defaultSort('ser')
            ->query(function (Trans_arc $tran){
                $tran=Trans_arc::where('main_id',$this->main_id);
                return $tran;
            })

            ->recordUrl(null)
            ->columns([
                TextColumn::make('ser')
                    ->size(TextSize::ExtraSmall)
                    ->color('primary')
                    ->sortable()
                    ->label('ت'),
                Tables\Columns\TextColumn::make('kst_date')
                    ->size(TextColumnSize::ExtraSmall)
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                    ->label('ت.الاستحقاق'),
                Tables\Columns\TextColumn::make('ksm_date')
                    ->size(TextColumnSize::ExtraSmall)
                    ->toggleable()

                    ->sortable()
                    ->label('ت.الخصم'),
                Tables\Columns\TextColumn::make('ksm')
                    ->size(TextColumnSize::ExtraSmall)
                    ->label('الخصم'),
                Tables\Columns\TextColumn::make('ksm_type_id')
                    ->size(TextColumnSize::ExtraSmall)
                    ->toggleable()
                    ->label('طريقة الدفع'),
                Tables\Columns\TextColumn::make('ksm_notes')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size(TextColumnSize::ExtraSmall)
                    ->label('ملاحظات'),
            ])
            ;
    }
}
