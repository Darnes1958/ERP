<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\Place;
use App\Models\Receipt;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
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

class RecSuppExl implements FromCollection,WithMapping,
    WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{
    private $data;
    public $filter;
    public $supplier_name;
    public $place_name;
    public $date1,$date2;
    /**
     * @return array
     */
    public function __construct(array $filter,Builder $data)
    {
        $this->filter=$filter;

        $this->data=$data;
        $place_name=Place::find($filter['place_id']->getState())->first();
        if ($place_name) $this->place_name=$place_name->name;
        $supplier_name=Supplier::find($filter['supplier_id']->getState())->first();
        if ($supplier_name) $this->supplier_name=$supplier_name->name;



        if ($filter['created_at']->getState()['Date1']) $this->date1='تاريخ من '.$filter['created_at']->getState()['Date1'];
        if ($filter['created_at']->getState()['Date2']) $this->date2='تاريخ حتي '.$filter['created_at']->getState()['Date2'];


    }

    /**
     * @var Receipt $rec
     */
    public function map($rec): array
    {
        if ($rec->Place) $place=$rec->Place->name; else $place=' ';
        return [
            $rec->id,
            $rec->receipt_date,
            $rec->Supplier->name,
            $rec->price_type->name,
            $rec->rec_who->name,
            $place,
            $rec->val,
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
            [''],
            [''],
            ['الرقم الألي','التاريخ','المورد','طريقة الدفع','البيان','دفعت من ','المبلغ','ملاحظات',]
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
                $event->sheet->getDelegate()->getStyle('D')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->setCellValue('C5', $this->supplier_name);
                $event->sheet->setCellValue('E5', $this->place_name);
                $event->sheet->setCellValue('B6', $this->date1);
                $event->sheet->setCellValue('D6', $this->date2);

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
            'F' => 40,
        ];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $rec=$this->data->get();

        return $rec;

    }
}
