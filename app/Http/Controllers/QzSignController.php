<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QzSignController extends Controller
{
    public function certificate(): Response
    {
        $path = config('printing.qz_certificate');

        abort_unless(is_readable($path), 404, 'QZ certificate not found. Run: php artisan qz:generate-keys');

        return response(
            file_get_contents($path),
            200,
            ['Content-Type' => 'text/plain']
        );
    }

    public function sign(Request $request): Response
    {
        $request->validate([
            'data' => ['required', 'string'],
        ]);

        $path = config('printing.qz_private_key');

        abort_unless(is_readable($path), 404, 'QZ private key not found. Run: php artisan qz:generate-keys');

        $privateKey = openssl_pkey_get_private(file_get_contents($path));

        abort_unless($privateKey !== false, 500, 'Invalid QZ private key.');

        $signature = '';

        openssl_sign($request->query('data'), $signature, $privateKey, OPENSSL_ALGO_SHA512);

        return response(
            base64_encode($signature),
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
