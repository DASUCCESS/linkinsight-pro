<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EnvironmentManager
{
    public function saveDatabaseConfig(array $data): void
    {
        $this->setEnvValues([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'],
        ]);

        Artisan::call('config:clear');
    }

    public function saveSmtpConfig(array $data): void
    {
        $this->setEnvValues([
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => $data['mail_host'],
            'MAIL_PORT' => $data['mail_port'],
            'MAIL_USERNAME' => $data['mail_username'],
            'MAIL_PASSWORD' => $data['mail_password'],
            'MAIL_ENCRYPTION' => $data['mail_encryption'],
            'MAIL_FROM_ADDRESS' => $data['mail_from_address'],
            'MAIL_FROM_NAME' => $data['mail_from_name'],
        ]);

        Artisan::call('config:clear');
    }

    public function markInstalled(): void
    {
        $this->setEnvValues([
            'APP_INSTALLED' => 'true',
        ]);

        Artisan::call('config:clear');
    }

    protected function setEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";

            $quotedValue = $this->quoteEnvValue($value);

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$quotedValue}", $content);
            } else {
                $content .= PHP_EOL."{$key}={$quotedValue}";
            }
        }

        file_put_contents($envPath, $content);
    }

    protected function quoteEnvValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        if (Str::contains($value, [' ', '#'])) {
            return '"'.addslashes($value).'"';
        }

        return $value;
    }
}
