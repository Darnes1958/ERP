<?php

namespace App\Exports;

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

class CustTranExl implements FromCollection,WithMapping,
  WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{
  public $cust_id;
  public $repDate;
  public $cust_name;
  public $mden;
  public $daen;

  /**
   * @return array
   */
  public function __construct(int $cust_id,string $repDate)
  {
    $this->cust_id=$cust_id;
    $this->repDate=$repDate;

  }

  /**
   * @var Cust_tran $rec
   */
  public function map($rec): array
  {

    return [
      $rec->repDate,
      $rec->id,
      $rec->rec_who->name,
      $rec->mden,
      $rec->daen,
      $rec->notes,
    ];
  }
  public function headings(): array
  {
    $cus=OurCompany::where('Company',Auth::user()->company)->first();
    return [
      ['      '.$cus->CompanyName],
      ['      '.$cus->CompanyNameSuffix],
      [' '],
      [''],
      [' '],
      [''],
      [''],
      ['التاريخ','الرقم الألي','البيان','مدين','دائن','ملاحظات',]
    ];
  }
  public function registerEvents(): array
  {
    return [

      AfterSheet::class => function(AfterSheet $event)  {
        $event->sheet
          ->getStyle('A8:F8')
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

        $event->sheet->setCellValue('C5', 'كشف حساب الزبون :  '.$this->cust_name.'      من تاريخ  '.$this->repDate);
        $event->sheet->setCellValue('E7','مدين : '.$this->mden.'    دارئن : '.$this->daen.'    الرصيد : '.$this->mden-$this->daen);
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
      'C' => 14,
      'D' => 14,
      'E' => 14,
      'F' => 40,
    ];
  }
  public function collection()
  {
    $rec=Cust_tran::where('customer_id',$this->cust_id)
      ->where('repDate','>=',$this->repDate)->get()
    ;
    $this->cust_name=Customer::find($this->cust_id)->name;
    $this->mden=Cust_tran::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
    $this->daen=Cust_tran::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');


    return $rec;
  }

}
