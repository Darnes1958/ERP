<?php

namespace App\Livewire\widget;

use App\Enums\Tar_type;
use App\Livewire\Traits\AksatTrait;
use App\Livewire\Traits\MainTrait;
use App\Models\Main;
use App\Models\Overkst;
use App\Models\Tarkst;
use App\Models\Tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;


class TarWidget extends BaseWidget
{

    protected static ?string $heading='';
    public $main_id;
    #[On('Take_Main_Id')]
    public function do($main_id)
    {
        $this->main_id=$main_id;
    }

    public function mount($main_id=null){
        $this->main_id=$main_id;
    }

    public function table(Table $table): Table
    {
        return $table

            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5,12,15,50])

            ->query(function (){
                $tran=Tarkst::where('tarkstable_id',$this->main_id);
                return $tran;
            })

           ->heading('مبالغ مرجعة')
            ->columns([
                TextColumn::make('ser')
                    ->label('ت')
                    ->color('primary')
                 ->rowIndex(),
                TextColumn::make('tar_date')
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('kst')
                    ->label('المبلغ'),
                TextColumn::make('tar_type')
                    ->label('البيان'),


            ])
            ->striped()
           ;
    }
}
