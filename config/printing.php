<?php

return [

    'label_printer_name' => env('LABEL_PRINTER_NAME', 'Xprinter XP-246B'),

    'direct_print_driver' => env('LABEL_PRINT_DRIVER', 'qz'),

    /*
    | QZ Tray uses PDF (Arabic supported). Server-side raw printing uses tspl.
    */
    'label_language' => env('LABEL_PRINT_LANGUAGE', 'tspl'),

    'label_width' => (int) env('LABEL_WIDTH', 30),

    'label_height' => (int) env('LABEL_HEIGHT', 40),

    'qz_certificate' => storage_path('app/qz/digital-certificate.txt'),

    'qz_private_key' => storage_path('app/qz/private-key.pem'),

    'qz_signing_enabled' => env('QZ_SIGNING_ENABLED', file_exists(storage_path('app/qz/private-key.pem'))),

];
