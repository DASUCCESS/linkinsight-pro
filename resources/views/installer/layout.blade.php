<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LinkInsight Pro Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite('resources/css/app.css')

    <style>
        body {
            background: radial-gradient(circle at top, #020617 0, #020617 30%, #020617 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center text-slate-100">
<div class="w-full max-w-3xl mx-auto px-4">
    <div class="bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-8 backdrop-blur">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">LinkInsight Pro Installer</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Guided setup wizard for first time installation.
                </p>
            </div>
            <div class="text-xs text-slate-500">
                Step @yield('step_number', '1') of 7
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/60 bg-red-900/30 px-4 py-3 text-sm text-red-100">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>
</body>
</html>
