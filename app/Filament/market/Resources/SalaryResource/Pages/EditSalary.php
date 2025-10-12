<?php

namespace App\Filament\market\Resources\SalaryResource\Pages;

use App\Filament\market\Resources\SalaryResource;
use App\Models\Salary;
use App\Models\Salarytran;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected ?string $heading='تعديل بيانات مرتب';
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('الغاء المرتب')
                ->visible(function (Salary $salary) {
                return !Salarytran::where('salary_id',$salary->id)->exists();
            }),
        ];
    }
}
