<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Item;
use App\Models\Item_tran;
use App\Models\OurCompany;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemTranExport extends DefaultValueBinder implements FromCollection,WithMapping,
  WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{
  public $item_id;
  public $repDate;
  public $item_name;

  /**
   * @return array
   */
  public function __construct(int $item_id,string $repDate)
  {
    $this->item_id=$item_id;
    $this->repDate=$repDate;

  }

  /**
   * @var Item_tran $rec
   */
  public function map($rec): array
  {

    return [
      $rec->created_at,
      $rec->type,
      $rec->order_date,
      $rec->id,
      $rec->name,
      $rec->price_type,
      $rec->notes,
      $rec->q1,
      $rec->price1,

      $rec->sub_tot,
    ];
  }
  public function headings(): array
  {
    $cus=OurCompany::where('Company',Auth::user()->company)->first();
    return [
      ['   '.$cus->CompanyName],
      ['   '.$cus->CompanyNameSuffix],
      [' '],
      [''],
      [' '],
      [''],
      [''],
      ['تاريخ الإدخال','البيان','تاريخ الفاتورة','رقم الفاتورة','العميل','طريقة الدفع','ملاحظات','الكمية','السعر','المجموع',]
    ];
  }
  public function registerEvents(): array
  {
    return [

      AfterSheet::class => function(AfterSheet $event)  {
        $event->sheet
          ->getStyle('A8:J8')
          ->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()
          ->setARGB('E8E1E1');
        $event->sheet->getDelegate()->getStyle('A')
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getDelegate()->getStyle('C')
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $event->sheet->setCellValue('D6', 'حركة الصنف :  '.$this->item_name.'      من تاريخ  '.$this->repDate);
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
      'D6'  => ['font' => ['bold' => true]],
      'A4'  => ['font' => ['bold' => true]],
      'B4'  => ['font' => ['bold' => true]],
      'A6'  => ['font' => ['bold' => true]],
    ];
  }
  public function columnFormats(): array
  {
    return [

      'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
      'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,

    ];
  }

        public function columnWidths(): array
  {
    return [
      'A' => 20,
      'B' => 14,
      'C' => 14,
      'D' => 14,
      'E' => 40,
      'F' => 14,
      'G' => 40,
      'H' => 14,
      'I' => 14,
      'J' => 18,

    ];
  }
    public function collection()
  {
    $rec=Item_tran::where('item_id',$this->item_id)
      ->where('order_date','>=',$this->repDate)->get()
    ;
    $this->item_name=Item::find($this->item_id)->name;

    return $rec;
  }

  }
