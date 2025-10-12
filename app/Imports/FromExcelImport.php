<?php

namespace App\Imports;

use App\Models\FromExcel;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FromExcelImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

      if (!isset($row['ksm'])  || !isset($row['acc'])
        || !isset($row['ksm_date'])) {
        return null;
      }



      $rec= FromExcel::on(auth()->user()->company)->create(
        [
          'name' => $row['name'],
          'acc' => $row['acc'],
          'ksm' => $row['ksm'],

          'ksm_date' => Date::excelToDateTimeObject($row['ksm_date']),

          'taj_id' => Auth::user()->taj,

        ]
      );

      return  $rec;
    }//
    public function headingRow(): int
        {
          return Auth::user()->headerrow;

        }

}
