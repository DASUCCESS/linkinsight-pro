<?php

namespace App\Services\Installer;

class RequirementsChecker
{
    public function check(): array
    {
        $results = [
            'php' => $this->checkPhp(),
            'extensions' => $this->checkExtensions(),
        ];

        $results['passed'] = $results['php']['passed'] && $results['extensions']['passed'];

        return $results;
    }

    protected function checkPhp(): array
    {
        $required = config('installer.required_php_version');
        $current = PHP_VERSION;

        $passed = version_compare($current, $required, '>=');

        return compact('required', 'current', 'passed');
    }

    protected function checkExtensions(): array
    {
        $requiredExtensions = config('installer.required_extensions');

        $missing = [];
        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        return [
            'required' => $requiredExtensions,
            'missing' => $missing,
            'passed' => count($missing) === 0,
        ];
    }
}
