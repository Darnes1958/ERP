<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Cust_tran;
use App\Models\Customer;
use App\Models\Customer_type;
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

class SuppRaseedExl implements FromCollection,WithMapping,
    WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{

    public $title;
    private $data;
    public $sum;
    public $rowcount;

    /**
     * @return array
     */
    public function __construct(string $title,$data,$sum)
    {
        $this->title=$title;
        $this->data=$data;
        $this->sum=$sum;
        $this->rowcount=$data->count();


    }

    /**
     * @var Cust_tran $rec
     */
    public function map($rec): array
    {

        return [

            $rec->name,
            $rec->mden,
            $rec->daen,
            $rec->raseed,
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
            ['الاسم','مدين','دائن','الرصيد',]
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event)  {
                $event->sheet
                    ->getStyle('A8:D8')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E8E1E1');

                $event->sheet->setCellValue('A5', $this->title);
                $event->sheet->getDelegate()->setRightToLeft(true);

                $event->sheet->setCellValue('A'.$this->rowcount+9, 'الإجمالـــــــــي');
                $event->sheet->setCellValue('B'.$this->rowcount+9, $this->sum->mden);
                $event->sheet->setCellValue('C'.$this->rowcount+9, $this->sum->daen);
                $event->sheet->setCellValue('D'.$this->rowcount+9, $this->sum->raseed);
                $event->sheet
                    ->getStyle('A'.($this->rowcount+9).':D'.$this->rowcount+9)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E8E1E1');


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

            'B'.$this->rowcount+9 => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]],
            'C'.$this->rowcount+9 => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]],
            'D'.$this->rowcount+9 => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]],
        ];
    }
    public function columnFormats(): array
    {
        return [

            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 14,
            'C' => 14,
            'D' => 14,

        ];
    }
    public function collection()
    {
        return $this->data;
    }

}
