<?php

namespace App\Exports;

use App\Filament\Pages\Reports\AccTran;
use App\Models\Acc_tran;
use App\Models\kazena;
use App\Models\kazena_tran;
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

class KazenaTranExl implements  FromCollection,WithMapping,
    WithHeadings,WithColumnWidths,WithEvents,WithStyles,WithColumnFormatting
{
    public $repDate1;
    public $repDate2;
    public $kazena_id;
    public $kazena_name;

    public $rowcount;
    public $sum_mden;
    public $sum_daen;

    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct(int $kazena_id,string $repDate1,string $repDate2)
    {
        $this->kazena_id=$kazena_id;
        $this->repDate1=$repDate1;
        $this->repDate2=$repDate2;

    }

    /**
     * @var AccTran $rec
     */
    public function map($rec): array
    {

        return [
            $rec->rec_who->name,
            $rec->name,
            $rec->receipt_date,
            $rec->mden,
            $rec->daen,
            $rec->order_id,
            $rec->notes,

        ];
    }
    public function headings(): array
    {
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        return [
            ['      '.$cus->CompanyName],
            ['               '.$cus->CompanyNameSuffix],
            [' '],
            [''],
            [' '],
            [''],
            [''],
            ['البيان','التفاصيل','التاريخ','مدين','دائن','رقم الفاتورة','ملاحظات',]
        ];
    }
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function(AfterSheet $event)  {
                $event->sheet
                    ->getStyle('A8:G8')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E8E1E1');

                $event->sheet->getDelegate()->getStyle('A')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('C')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->setCellValue('B5', 'كشف حساب الخزينة :  '.$this->kazena_name.'      من تاريخ  '.$this->repDate1.'     إلي تاريخ '.$this->repDate2);

                $event->sheet->getDelegate()->setRightToLeft(true);

                $event->sheet->setCellValue('A'.$this->rowcount+9, 'الإجمالـــــــــي');
                $event->sheet->setCellValue('D'.$this->rowcount+9, $this->sum_mden);
                $event->sheet->setCellValue('E'.$this->rowcount+9, $this->sum_daen);
                $event->sheet
                    ->getStyle('A'.($this->rowcount+9).':G'.$this->rowcount+9)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
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
            'D'.$this->rowcount+9  => ['font' => ['bold' => true]],
            'C'.$this->rowcount+9  => ['font' => ['bold' => true]],

            'C'.$this->rowcount+9 => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]],
            'D'.$this->rowcount+9 => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]],
        ];
    }
    public function columnFormats(): array
    {
        return [

            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,

        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 14,
            'G' => 40,
        ];
    }
    public function collection()
    {

        $rec=Acc_tran::
        where('kazena_id',$this->kazena_id)
            ->where('kazena_id','!=',null)
            ->when($this->repDate1,function ($q){
                $q->where('receipt_date','>=',$this->repDate1);
            })
            ->when($this->repDate2,function ($q){
                $q->where('receipt_date','<=',$this->repDate2);
            })
            ->get()

        ;
        $this->kazena_name=kazena::find($this->kazena_id)->name;
        $this->rowcount=$rec->count();
        $this->sum_mden=$rec->sum('mden');
        $this->sum_daen=$rec->sum('daen');

        return $rec;
    }
}
