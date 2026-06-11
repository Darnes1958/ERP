<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateQzKeys extends Command
{
    protected $signature = 'qz:generate-keys';

    protected $description = 'Generate QZ Tray signing certificate and private key';

    public function handle(): int
    {
        $directory = storage_path('app/qz');
        $certificatePath = config('printing.qz_certificate');
        $privateKeyPath = config('printing.qz_private_key');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        putenv('OPENSSL_CONF='.$directory.DIRECTORY_SEPARATOR.'openssl.cnf');

        $opensslConfig = $directory.DIRECTORY_SEPARATOR.'openssl.cnf';

        $config = [
            'config' => $opensslConfig,
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'digest_alg' => 'sha512',
        ];

        $resource = openssl_pkey_new($config);

        if ($resource === false) {
            $this->error('Unable to generate private key.');

            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privateKey);

        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'] ?? null;

        if (! $publicKey) {
            $this->error('Unable to extract public key.');

            return self::FAILURE;
        }

        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($certificatePath, $publicKey);

        $this->info('QZ keys generated:');
        $this->line('  Certificate: '.$certificatePath);
        $this->line('  Private key: '.$privateKeyPath);
        $this->newLine();
        $this->line('Add this line to QZ Tray override.properties:');
        $this->line('authcert.override='.str_replace('\\', '\\\\', $certificatePath));

        return self::SUCCESS;
    }
}
