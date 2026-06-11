<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <style>
        @page {
            size: {{ $width }}mm {{ $height }}mm;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .label-page {
            width: {{ $width }}mm;
            height: {{ $height }}mm;
            overflow: hidden;
            page-break-after: always;
            box-sizing: border-box;
        }

        .label-page:last-child {
            page-break-after: avoid;
        }

        .label {
            width: {{ $width - 3 }}mm;
            margin: 2mm 1mm 0 auto;
            text-align: right;
        }

        .id {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 1.5mm;
            text-align: right;
        }

        .name {
            font-size: 12px;
            line-height: 1.3;
            text-align: right;
            direction: rtl;
            unicode-bidi: plaintext;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
@foreach($res as $item)
    <div class="label-page">
        <div class="label">
            <div class="id">رقم: {{ $item->id }}</div>
            <div class="name">{{ $item->name }}</div>
        </div>
    </div>
@endforeach
</body>
</html>
