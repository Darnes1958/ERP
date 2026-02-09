<?php

namespace App\Exports;

use App\Models\Bank;
use App\Models\Main;
use App\Models\Taj;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Cust_tran;
use App\Models\Customer;
use App\Models\OurCompany;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MotakraExl implements FromCollection,WithMapping,
  WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{
  public $Baky;
  public $name;
  public $data;
  public $cus;



  /**
   * @return array
   */
  public function __construct(int $baky,string $name,$data)
  {
    $this->Baky=$baky;
    $this->name=$name;
    $this->data=$data;
      $this->cus=OurCompany::where('Company',Auth::user()->company)->first();

  }

  /**
   * @var Main $rec
   */
  public function map($rec): array
  {

    return [
      $rec->id,
      $rec->acc,
      $rec->Customer->name,
      $rec->sul,
      $rec->kst,
      $rec->pay,
      $rec->raseed,
      $rec->Late,
      $rec->LastKsm
    ];
  }
  public function headings(): array
  {
    $cus=OurCompany::where('Company',Auth::user()->company)->first();
    return [
      ['                       '.$cus->CompanyName],
      ['                             '.$cus->CompanyNameSuffix],
      [' '],
      [''],
      [' '],
      [''],
      [''],
      ['رقم العقد','رقم الحساب','الاسم','اجمالي العقد','القسط','المدفوع','المتبقي','عدد المتأخرة','تاريخ أخر قسط',]
    ];
  }
  public function registerEvents(): array
  {

    return [

      AfterSheet::class => function(AfterSheet $event)  {

        $event->sheet
          ->getStyle('A8:I8')
          ->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()
          ->setARGB('E8E1E1');
        $event->sheet->getDelegate()->getStyle('A')
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getDelegate()->getStyle('B')
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getDelegate()->getStyle('C')
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $event->sheet->setCellValue('C5', 'تقرير بالعقود المتاخرة السداد حتي تاريخ :  :  '.now());
        $event->sheet->setCellValue('I5','المصرف : '.$this->name);
        $event->sheet->getDelegate()->setRightToLeft(true);

      },
    ];
  }
  public function styles(Worksheet $sheet)
  {
    return [
      8    => ['font' => ['bold' => true]],
      'A1'  => ['font' => ['size' => 20]],
      'A2'  => ['font' => ['size' => 18]],
      'C5'  => ['font' => ['bold' => true]],
      'I5'  => ['font' => ['bold' => true]],
      'A4'  => ['font' => ['bold' => true]],
      'B4'  => ['font' => ['bold' => true]],
      'A6'  => ['font' => ['bold' => true]],
    ];
  }
  public function columnFormats(): array
  {
    return [

      'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
      'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,

    ];
  }

  public function columnWidths(): array
  {
    return [
      'A' => 14,
      'B' => 14,
      'C' => 30,
      'D' => 14,
      'E' => 14,
      'F' => 14,
        'G' => 14,
        'H' => 14,
        'I' => 20,
    ];
  }
  public function collection()
  {

    return $this->data;
  }

}
