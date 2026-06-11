<?php

namespace App\Services;

use App\Models\GlobalSetting;
use App\Models\Item;
use App\Models\OurCompany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Throwable;

class ItemLabelPrintService
{
    public function shouldPrintOnServer(): bool
    {
        $driver = config('printing.direct_print_driver', 'qz');

        if ($driver === 'qz') {
            return false;
        }

        if ($driver === 'server') {
            return PHP_OS_FAMILY === 'Windows';
        }

        return app()->environment('local') && PHP_OS_FAMILY === 'Windows';
    }

    public function shouldFallbackToQz(): bool
    {
        return config('printing.direct_print_driver', 'qz') === 'auto';
    }

    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public function buildPdfBase64(Collection|Item $items): string
    {
        $items = $items instanceof Item ? collect([$items]) : $items;
        $width = config('printing.label_width', 30);
        $height = config('printing.label_height', 40);
        $single = $items->count() === 1;

        $pdf = Pdf::view(
            $single ? 'PDF.ItemLabel' : 'PDF.ItemLabels',
            [
                'res' => $single ? $items->first() : $items,
                'arr' => [],
                'cus' => OurCompany::where('Company', Auth::user()->company)->first(),
                'width' => $width,
                'height' => $height,
            ]
        )
            ->paperSize($width, $height)
            ->margins(0, 0, 0, 0)
            ->footerView('PDF.empty')
            ->withBrowsershot(function (Browsershot $shot) use ($single) {
                $shot->noSandbox()
                    ->setChromePath(GlobalSetting::first()->exePath)
                    ->margins(0, 0, 0, 0);

                if ($single) {
                    $shot->pages('1');
                }
            });

        return $pdf->base64();
    }

    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public function buildRaw(Collection|Item $items): string
    {
        return config('printing.label_language', 'tspl') === 'tspl'
            ? $this->buildTspl($items)
            : $this->buildEscPos($items);
    }

    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public function buildTspl(Collection|Item $items): string
    {
        $items = $items instanceof Item ? collect([$items]) : $items;
        $width = config('printing.label_width', 30);
        $height = config('printing.label_height', 40);
        $commands = '';

        foreach ($items as $item) {
            $id = $this->escapeTspl('رقم: '.$item->id);
            $name = $this->escapeTspl($item->name);

            $commands .= "SIZE {$width} mm, {$height} mm\r\n";
            $commands .= "GAP 2 mm, 0 mm\r\n";
            $commands .= "DIRECTION 1\r\n";
            $commands .= "REFERENCE 0,0\r\n";
            $commands .= "CLS\r\n";
            $commands .= "TEXT 20,40,\"3\",0,1,1,\"{$id}\"\r\n";
            $commands .= "TEXT 20,90,\"2\",0,1,1,\"{$name}\"\r\n";
            $commands .= "PRINT 1,1\r\n";
        }

        return $commands;
    }

    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public function buildEscPos(Collection|Item $items): string
    {
        $items = $items instanceof Item ? collect([$items]) : $items;

        $connector = new DummyPrintConnector;
        $printer = new Printer($connector);
        $this->writeEscPosLabels($printer, $items);
        $data = $connector->getData();
        $printer->close();

        return $data;
    }

    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public function printOnServer(Collection|Item $items): void
    {
        $items = $items instanceof Item ? collect([$items]) : $items;
        $raw = $this->buildRaw($items);

        if (config('printing.label_language', 'tspl') === 'tspl') {
            $this->sendRawToWindowsPrinter($raw);

            return;
        }

        $connector = $this->makeServerConnector();
        $printer = new Printer($connector);

        try {
            $this->writeEscPosLabels($printer, $items);
        } finally {
            $printer->close();
        }
    }

    protected function sendRawToWindowsPrinter(string $raw): void
    {
        $connector = new WindowsPrintConnector(config('printing.label_printer_name'));
        $connector->write($raw);
        $connector->finalize();
    }

    protected function makeServerConnector(): PrintConnector
    {
        return new WindowsPrintConnector(config('printing.label_printer_name'));
    }

    /**
     * @param  Collection<int, Item>  $items
     */
    protected function writeEscPosLabels(Printer $printer, Collection $items): void
    {
        foreach ($items as $item) {
            $this->writeEscPosLabel($printer, $item);
        }
    }

    protected function writeEscPosLabel(Printer $printer, Item $item): void
    {
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->setEmphasis(true);
        $printer->text('رقم: '.$item->id."\n");
        $printer->setEmphasis(false);
        $printer->text($item->name."\n");
        $printer->feed(3);
    }

    protected function escapeTspl(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    public function serverPrintErrorMessage(Throwable $exception): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return 'الطباعة المباشرة من السيرفر متاحة على Windows فقط. استخدم QZ Tray في الإنتاج.';
        }

        return 'تعذر الاتصال بالطابعة «'.config('printing.label_printer_name').'»: '.$exception->getMessage();
    }
}
