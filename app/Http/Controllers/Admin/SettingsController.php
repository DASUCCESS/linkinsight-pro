<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Setting;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(
        protected EnvironmentManager $environmentManager,
        protected InstallerService $installerService
    ) {
    }

    public function edit()
    {
        $general = [
            'site_name'   => Setting::getValue('general', 'site_name', config('app.name')),
            'timezone'    => Setting::getValue('general', 'timezone', config('app.timezone')),
            'locale'      => Setting::getValue('general', 'locale', config('app.locale')),
            'date_format' => Setting::getValue('general', 'date_format', 'Y-m-d'),
            'logo'        => Setting::getValue('general', 'logo'),
            'favicon'     => Setting::getValue('general', 'favicon'),
        ];

        $appearance = [
            'primary_color'   => Setting::getValue('appearance', 'primary_color', '#4f46e5'),
            'secondary_color' => Setting::getValue('appearance', 'secondary_color', '#0ea5e9'),
            'accent_color'    => Setting::getValue('appearance', 'accent_color', '#f97316'),
            'background_color'=> Setting::getValue('appearance', 'background_color', '#020617'),
            'card_color'      => Setting::getValue('appearance', 'card_color', '#0f172a'),
            'border_color'    => Setting::getValue('appearance', 'border_color', '#1e293b'),
            'text_primary'    => Setting::getValue('appearance', 'text_primary', '#e5e7eb'),
            'text_secondary'  => Setting::getValue('appearance', 'text_secondary', '#9ca3af'),
            'button_radius'   => Setting::getValue('appearance', 'button_radius', '0.75rem'),
            'hover_scale'     => Setting::getValue('appearance', 'hover_scale', '1.05'),
        ];

        $seo = [
            'meta_title'       => Setting::getValue('seo', 'meta_title'),
            'meta_description' => Setting::getValue('seo', 'meta_description'),
            'meta_keywords'    => Setting::getValue('seo', 'meta_keywords'),
            'og_title'         => Setting::getValue('seo', 'og_title'),
            'og_description'   => Setting::getValue('seo', 'og_description'),
            'og_image'         => Setting::getValue('seo', 'og_image'),
            'canonical_url'    => Setting::getValue('seo', 'canonical_url'),
            'robots_txt'       => Setting::getValue('seo', 'robots_txt'),
            'sitemap_enabled'  => Setting::getValue('seo', 'sitemap_enabled', true),
        ];

        // Auth settings
        $auth = [
            'require_email_verification' => Setting::getValue('auth', 'require_email_verification', true),
        ];

        $smtp = [
            'mail_host'        => config('mail.mailers.smtp.host'),
            'mail_port'        => config('mail.mailers.smtp.port'),
            'mail_username'    => config('mail.mailers.smtp.username'),
            'mail_encryption'  => config('mail.mailers.smtp.encryption'),
            'mail_from_address'=> config('mail.from.address'),
            'mail_from_name'   => config('mail.from.name'),
        ];

        $license = License::latest('id')->first();

        return view('admin.settings.index', compact('general', 'appearance', 'seo', 'auth', 'smtp', 'license'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:190',
            'timezone'  => 'required|string',
            'locale'    => 'required|string',
            'date_format' => ['required', 'string', 'max:32'],
            'logo'      => 'nullable|image|max:2048',
            'favicon'   => 'nullable|image|max:1024',
        ]);

        Setting::setValue('general', 'site_name', $validated['site_name']);
        Setting::setValue('general', 'timezone', $validated['timezone']);
        Setting::setValue('general', 'locale', $validated['locale']);
        Setting::setValue('general', 'date_format', $validated['date_format']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('uploads/settings', 'public');
            Setting::setValue('general', 'logo', $path);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('uploads/settings', 'public');
            Setting::setValue('general', 'favicon', $path);
        }

        return back()->with('status_general', 'General settings updated successfully.');
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'primary_color'   => 'required|string|max:16',
            'secondary_color' => 'required|string|max:16',
            'accent_color'    => 'required|string|max:16',
            'background_color'=> 'required|string|max:16',
            'card_color'      => 'required|string|max:16',
            'border_color'    => 'required|string|max:16',
            'text_primary'    => 'required|string|max:16',
            'text_secondary'  => 'required|string|max:16',
            'button_radius'   => 'required|string|max:16',
            'hover_scale'     => ['required', 'regex:/^[0-9]*\.?[0-9]+$/'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue('appearance', $key, $value);
        }

        return back()->with('status_appearance', 'Appearance settings updated successfully.');
    }

    public function updateSeo(Request $request)
    {
        $validated = $request->validate([
            'meta_title'       => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'og_title'         => 'nullable|string|max:190',
            'og_description'   => 'nullable|string|max:500',
            'og_image'         => 'nullable|url',
            'canonical_url'    => 'nullable|url',
            'robots_txt'       => 'nullable|string',
            'sitemap_enabled'  => ['required', Rule::in(['0', '1'])],
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'sitemap_enabled') {
                $value = (bool) $value;
            }
            Setting::setValue('seo', $key, $value);
        }

        return back()->with('status_seo', 'SEO settings updated successfully.');
    }

    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'mail_host'        => 'required|string',
            'mail_port'        => 'required|numeric',
            'mail_username'    => 'required|string',
            'mail_password'    => 'nullable|string',
            'mail_encryption'  => 'nullable|string',
            'mail_from_address'=> 'required|email',
            'mail_from_name'   => 'required|string',
        ]);

        $envData = [
            'mail_host'        => $validated['mail_host'],
            'mail_port'        => $validated['mail_port'],
            'mail_username'    => $validated['mail_username'],
            'mail_password'    => $validated['mail_password'] ?? env('MAIL_PASSWORD'),
            'mail_encryption'  => $validated['mail_encryption'] ?? '',
            'mail_from_address'=> $validated['mail_from_address'],
            'mail_from_name'   => $validated['mail_from_name'],
        ];

        $this->environmentManager->saveSmtpConfig($envData);

        return back()->with('status_smtp', 'SMTP settings updated. You can send a test email to confirm.');
    }

    public function testSmtp(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        $ok = $this->installerService->testSmtp($validated['test_email']);

        if (! $ok) {
            return back()->withErrors(['smtp_test' => 'SMTP test failed. Check your credentials and try again.']);
        }

        return back()->with('status_smtp_test', 'Test email sent successfully.');
    }

    /**
     * Auth settings: email verification toggle.
     */
    public function updateAuth(Request $request)
    {
        $validated = $request->validate([
            'require_email_verification' => ['required', Rule::in(['0', '1'])],
        ]);

        $value = $validated['require_email_verification'] === '1';

        Setting::setValue('auth', 'require_email_verification', $value);

        return back()->with('status_auth', 'Auth settings updated successfully.');
    }
}
