<?php

namespace App\Filament\Resources\SalaryResource\Pages;

use App\Filament\Resources\SalaryResource;
use App\Models\Salary;
use App\Models\Salarytran;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected ?string $heading='تعديل بيانات مرتب';
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading('الغاء المرتب')
                ->visible(function (Salary $salary) {
                return !Salarytran::where('salary_id',$salary->id)->exists();
            }),
        ];
    }
}
