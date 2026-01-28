<?php

namespace App\Services\Installer;

use App\Models\License;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LicenseValidator
{
    public function activateWithPurchaseCode(string $purchaseCode, string $email, string $domain): License
    {
        $serverUrl = rtrim(config('license.server_url'), '/');
        $itemId = config('license.item_id');

        $response = Http::timeout(15)->post("{$serverUrl}/api/activate", [
            'purchase_code' => $purchaseCode,
            'email' => $email,
            'domain' => $domain,
            'item_id' => $itemId,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('License server error. Please try again later.');
        }

        $data = $response->json();

        if (! ($data['success'] ?? false)) {
            throw new \RuntimeException($data['message'] ?? 'License activation failed.');
        }

        return License::updateOrCreate(
            ['purchase_code' => $purchaseCode],
            [
                'buyer' => $data['buyer'] ?? null,
                'email' => $email,
                'domain' => $domain,
                'item_id' => $itemId,
                'license_token' => $data['token'] ?? Str::random(40),
                'status' => 'active',
                'last_checked_at' => now(),
                'support_ends_at' => $data['support_ends_at'] ?? null,
                'is_owner_license' => false,
            ]
        );
    }

    public function activateOwnerLicense(string $domain): License
    {
        $itemId = config('license.item_id');

        return License::updateOrCreate(
            ['purchase_code' => 'OWNER-'.strtoupper($itemId)],
            [
                'buyer' => 'Owner',
                'email' => null,
                'domain' => $domain,
                'item_id' => $itemId,
                'license_token' => Str::random(60),
                'status' => 'active',
                'last_checked_at' => now(),
                'support_ends_at' => null,
                'is_owner_license' => true,
            ]
        );
    }
}
