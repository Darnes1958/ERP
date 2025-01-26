<?php

namespace App\Exports;

use App\Models\OurCompany;

use App\Models\Salarytran;
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

class SalaryTranExl implements FromCollection,WithMapping,
    WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{

    public $raseed;
    public $name;
    private $data;
    public $rep;
    /**
     * @return array
     */
    public function __construct(string $name,$raseed,$data,$rep)
    {


        $this->raseed=$raseed;
        $this->rep=$rep;
        if ($rep=='sal') $this->name= 'كشف حساب مرتب الموظف  :  '.$name;
        if ($rep=='rent') $this->name= 'كشف حساب إيجار  :  '.$name;
        $this->data=$data;
    }

    /**
     * @var Salarytran $rec
     */
    public function map($rec): array
    {
        $name=' ';
        if ($rec->kazena_id) $name=$rec->Kazena->name;
        if ($rec->acc_id) $name=$rec->Acc->name;

        $month=$rec->month;
        if ($month=='0') $month=' ';

        return [
            $rec->tran_date,
            $rec->tran_type,
            $name,
            $month,
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
            ['التاريخ','البيان','دفعت من ','عن شهر','المبلغ','ملاحظات',]
        ];
    }
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function(AfterSheet $event)  {
                $event->sheet
                    ->getStyle('A8:F8')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E8E1E1');
                $event->sheet->getDelegate()->getStyle('A')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('D')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->setCellValue('C5', $this->name);
                if ($this->rep=='sal')
                    $event->sheet->setCellValue('E7','    الرصيد : '.$this->raseed);
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
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rec=$this->data;
        return $rec;

    }
}
