<?php

namespace App\Services\Installer;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InstallerService
{
    public function testDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable $e) {
            Log::error('Installer database connection failed', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function runMigrationsAndSeed(): void
    {
        Log::info('Installer: running migrations');
        Artisan::call('migrate', ['--force' => true]);
        Log::info('Installer: migrate output: '.Artisan::output());

        Log::info('Installer: running seeders');
        Artisan::call('db:seed', ['--force' => true]);
        Log::info('Installer: seed output: '.Artisan::output());
    }

    public function createAdminAccount(array $data): User
    {
        return User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'],
                'password' => Hash::make($data['password']),
            ]
        );
    }

    public function saveGeneralSettings(array $data): void
    {
        Setting::setValue('general', 'site_name', $data['site_name']);
        Setting::setValue('general', 'timezone', $data['timezone'] ?? config('app.timezone'));
        Setting::setValue('general', 'locale', $data['locale'] ?? config('app.locale'));
    }

    public function testSmtp(string $toEmail): bool
    {
        // For local development, you can choose to skip SMTP test entirely:
        if (app()->environment('local')) {
            Log::info('Installer SMTP test skipped in local environment');

            return true;
        }

        try {
            Mail::raw('LinkInsight Pro SMTP test mail', function ($message) use ($toEmail) {
                $message->to($toEmail)->subject('SMTP Test');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Installer SMTP test failed', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
