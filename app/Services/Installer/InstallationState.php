<?php

namespace App\Services\Installer;

use App\Models\License;
use Illuminate\Support\Facades\Schema;

class InstallationState
{
    public function isInstalled(): bool
    {
        try {
            if (! Schema::hasTable('licenses')) {
                return false;
            }

            return License::query()
                ->where('status', 'active')
                ->whereNotNull('license_token')
                ->where('license_token', '!=', '')
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
