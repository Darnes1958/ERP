<?php

namespace App\Filament\market\Resources\ReceiptResource\Pages;

use App\Exports\ReceiptExl;
use App\Exports\SalaryTranExl;
use App\Filament\market\Resources\ReceiptResource;
use App\Models\Customer;
use App\Models\Place;
use App\Models\Salary;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Container\Attributes\Database;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;




  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">ايصالات قبض ودفع</div>');
  }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->createAnother(false)
             ->label('إضافة إيصال'),
            Action::make('Exl')
                ->label('Excel')

                ->button()
                ->color('success')
                ->action(function (){

                    return Excel::download(new ReceiptExl(
                       $this->table->getFilters(), $this->getTableQueryForExport()),'receipt.xlsx');
                }),


        ];
    }
}
