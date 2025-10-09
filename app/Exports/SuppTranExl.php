<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
use App\Models\OurCompany;

use App\Models\Supp_tran2;
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

class SuppTranExl implements FromCollection,WithMapping,
  WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{

    public $repDate;
    private $supp_id;

    public $mden;
    public $daen;
    public $raseed;
    public $supp_name;
    /**
     * @return array
     */
    public function __construct(string $supp_name,$repDate,$data,$mden,$daen,$raseed)
    {
        $this->supp_name=$supp_name;
        $this->repDate=$repDate;
        $this->data=$data;
        $this->mden=$mden;
        $this->daen=$daen;
        $this->raseed=$raseed;

    }

    /**
     * @var Supp_tran2 $rec
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

                $event->sheet->setCellValue('C5', 'كشف حساب المورد :  '.$this->supp_name.'      من تاريخ  '.$this->repDate);
                $event->sheet->setCellValue('E7','مدين : '.$this->mden.'    دارئن : '.$this->daen.'    الرصيد : '.$this->raseed);
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

    /**
     * @return Collection
     */
    public function collection()
    {
        $rec=$this->data;
        return $rec;

    }
}
