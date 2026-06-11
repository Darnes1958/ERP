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
            width: {{ $width }}mm;
            height: {{ $height }}mm;
            overflow: hidden;
            color: #000;
            background: #fff;
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
<div class="label">
    <div class="id">رقم: {{ $res->id }}</div>
    <div class="name">{{ $res->name }}</div>
</div>
</body>
</html>
