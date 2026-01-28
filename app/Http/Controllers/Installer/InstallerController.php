<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallerService;
use App\Services\Installer\LicenseValidator;
use App\Services\Installer\PermissionsChecker;
use App\Services\Installer\RequirementsChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class InstallerController extends Controller
{
    public function __construct(
        protected RequirementsChecker $requirementsChecker,
        protected PermissionsChecker $permissionsChecker,
        protected EnvironmentManager $environmentManager,
        protected InstallerService $installerService,
        protected LicenseValidator $licenseValidator,
    ) {
    }

    public function requirements()
    {
        $results = $this->requirementsChecker->check();

        return view('installer.requirements', compact('results'));
    }

    public function requirementsNext(Request $request)
    {
        $results = $this->requirementsChecker->check();

        if (! $results['passed']) {
            return back()->withErrors(['requirements' => 'Server requirements not satisfied.']);
        }

        return redirect()->route('installer.permissions');
    }

    public function permissions()
    {
        $results = $this->permissionsChecker->check();

        return view('installer.permissions', compact('results'));
    }

    public function permissionsNext(Request $request)
    {
        $results = $this->permissionsChecker->check();

        if (! $results['passed']) {
            return back()->withErrors(['permissions' => 'Required folders are not writable.']);
        }

        return redirect()->route('installer.database');
    }

    public function database()
    {
        return view('installer.database');
    }

    public function databaseSave(Request $request)
    {
        $validated = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $this->environmentManager->saveDatabaseConfig($validated);

        if (! $this->installerService->testDatabaseConnection()) {
            return back()->withErrors(['database' => 'Cannot connect to the database with the provided details.'])->withInput();
        }

        $this->installerService->runMigrationsAndSeed();

        return redirect()->route('installer.admin');
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function adminSave(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:190',
            'timezone' => 'required|string',
            'locale' => 'required|string',
            'admin_name' => 'required|string|max:190',
            'admin_email' => 'required|email|max:190',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $this->installerService->createAdminAccount([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => $validated['admin_password'],
        ]);

        $this->installerService->saveGeneralSettings([
            'site_name' => $validated['site_name'],
            'timezone' => $validated['timezone'],
            'locale' => $validated['locale'],
        ]);

        return redirect()->route('installer.smtp')->with('admin_email', $validated['admin_email']);
    }

    public function smtp(Request $request)
    {
        $testEmail = $request->session()->get('admin_email');

        return view('installer.smtp', compact('testEmail'));
    }

    public function smtpSave(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'test_email' => 'nullable|email',
        ]);

        $this->environmentManager->saveSmtpConfig($validated);

        $testEmail = $validated['test_email'] ?: $validated['mail_from_address'];

        $ok = $this->installerService->testSmtp($testEmail);

        if (! $ok) {
            return back()->withErrors(['smtp' => 'SMTP test failed. Please verify credentials.'])->withInput();
        }

        return redirect()->route('installer.license');
    }

    public function license(Request $request)
    {
        $appUrl = URL::to('/');

        return view('installer.license', compact('appUrl'));
    }

    public function licenseSave(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|in:codecanyon,external,owner',
            'purchase_code' => 'nullable|string',
            'email' => 'nullable|email',
            'domain' => 'required|string',
        ]);

        if ($validated['mode'] === 'owner') {
            $this->licenseValidator->activateOwnerLicense($validated['domain']);
        } else {
            if (! $validated['purchase_code'] || ! $validated['email']) {
                return back()->withErrors(['license' => 'Purchase code and email are required for this mode.']);
            }

            $this->licenseValidator->activateWithPurchaseCode(
                $validated['purchase_code'],
                $validated['email'],
                $validated['domain']
            );
        }

        $this->environmentManager->markInstalled();

        return redirect()->route('installer.finish');
    }

    public function finish()
    {
        return view('installer.finish');
    }
}
