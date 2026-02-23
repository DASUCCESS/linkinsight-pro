<?php

namespace App\Services\Installer;

use App\Models\License;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LicenseValidator
{
    /**
     * All verification decisions are handled by the license server.
     */
    public function activateLicense(
        ?string $purchaseCode,
        ?string $verificationCode,
        ?string $email,
        string $domain
    ): License {
        $serverUrl = rtrim(config('license.server_url'), '/');
        $itemId    = config('license.item_id');

        $payload = [
            'domain' => $domain,
            'item_id'=> $itemId,
        ];

        if ($email) {
            $payload['email'] = $email;
        }

        if ($purchaseCode) {
            $payload['purchase_code'] = $purchaseCode;
        }

        if ($verificationCode) {
            $payload['verification_code'] = $verificationCode;
        }

        $response = Http::timeout(15)->post("{$serverUrl}/api/license/activate", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('License server error. Please try again later.');
        }

        $data = $response->json();

        if (! ($data['success'] ?? false) || ($data['status'] ?? null) !== 'ok') {
            throw new \RuntimeException($data['message'] ?? 'License activation failed.');
        }

        $licenseToken   = $data['license_token'] ?? Str::random(60);
        $purchaseCodeDb = $data['purchase_code'] ?? ($purchaseCode ?: 'MANUAL-' . Str::upper(Str::random(10)));
        $supportEnds    = $data['support_ends_at'] ?? null;

        return License::updateOrCreate(
            ['purchase_code' => $purchaseCodeDb],
            [
                'buyer'           => $data['buyer'] ?? null,
                'email'           => $data['email'] ?? $email,
                'domain'          => $data['domain'] ?? $domain,
                'item_id'         => $data['item_id'] ?? $itemId,
                'license_token'   => $licenseToken,
                'status'          => 'active',
                'last_checked_at' => now(),
                'support_ends_at' => $supportEnds,
                'is_owner_license'=> (bool) ($data['is_owner_license'] ?? false),
            ]
        );
    }
}
