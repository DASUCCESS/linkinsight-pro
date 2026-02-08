@extends('admin.layout')

@section('page_title', 'Settings')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Left: Sections nav + License card --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-xl p-4">
                <h2 class="text-sm font-semibold text-slate-700 mb-3">Settings Sections</h2>
                <div class="space-y-2 text-sm">
                    <a href="#general"
                       class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer
                              bg-slate-50 border border-slate-200 text-slate-700
                              transition transform duration-150 hover:bg-sky-50 hover:border-sky-200 hover:shadow-md hover:scale-[var(--hover-scale)]">
                        <span>General</span>
                        <span class="h-6 w-6 rounded-xl flex items-center justify-center bg-slate-100 text-[10px] text-slate-500">
                            01
                        </span>
                    </a>
                    <a href="#appearance"
                       class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer
                              bg-slate-50 border border-slate-200 text-slate-700
                              transition transform duration-150 hover:bg-sky-50 hover:border-sky-200 hover:shadow-md hover:scale-[var(--hover-scale)]">
                        <span>Appearance</span>
                        <span class="h-6 w-6 rounded-xl flex items-center justify-center bg-slate-100 text-[10px] text-slate-500">
                            02
                        </span>
                    </a>
                    <a href="#seo"
                       class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer
                              bg-slate-50 border border-slate-200 text-slate-700
                              transition transform duration-150 hover:bg-sky-50 hover:border-sky-200 hover:shadow-md hover:scale-[var(--hover-scale)]">
                        <span>SEO</span>
                        <span class="h-6 w-6 rounded-xl flex items-center justify-center bg-slate-100 text-[10px] text-slate-500">
                            03
                        </span>
                    </a>
                    <a href="#smtp"
                       class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer
                              bg-slate-50 border border-slate-200 text-slate-700
                              transition transform duration-150 hover:bg-sky-50 hover:border-sky-200 hover:shadow-md hover:scale-[var(--hover-scale)]">
                        <span>SMTP</span>
                        <span class="h-6 w-6 rounded-xl flex items-center justify-center bg-slate-100 text-[10px] text-slate-500">
                            04
                        </span>
                    </a>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-xl p-4 text-sm">
                <h2 class="text-sm font-semibold text-slate-700 mb-3">License Status</h2>
                @if($license)
                    <div class="space-y-1">
                        <p class="flex items-center justify-between">
                            <span class="text-slate-500">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                         @if($license->status === 'active')
                                             bg-emerald-50 text-emerald-600 border border-emerald-200
                                         @else
                                             bg-rose-50 text-rose-600 border border-rose-200
                                         @endif">
                                {{ ucfirst($license->status) }}
                            </span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-slate-500">Domain</span>
                            <span class="text-slate-700 truncate max-w-[150px] text-right">{{ $license->domain ?? '-' }}</span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-slate-500">Item ID</span>
                            <span class="text-slate-700">{{ $license->item_id ?? '-' }}</span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-slate-500">Last Check</span>
                            <span class="text-slate-700 text-right">
                                {{ optional($license->last_checked_at)->toDayDateTimeString() ?? '-' }}
                            </span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-slate-500">Support Ends</span>
                            <span class="text-slate-700">
                                {{ optional($license->support_ends_at)->toDateString() ?? '-' }}
                            </span>
                        </p>
                        @if($license->is_owner_license)
                            <p class="text-[11px] text-amber-500 mt-2">
                                Owner license detected. Validation bypass is enabled on this domain.
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-slate-500 text-sm">
                        No license record found. Run the installer or configure the license on the server.
                    </p>
                @endif>
            </div>
        </div>

        {{-- Right: Forms --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- General --}}
            <section id="general"
                     class="bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">General Settings</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Configure basic application identity and locale options.
                        </p>
                    </div>
                    @if(session('status_general'))
                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                            {{ session('status_general') }}
                        </span>
                    @endif
                </div>

                <form method="POST"
                      action="{{ route('admin.settings.general.update') }}"
                      enctype="multipart/form-data"
                      class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Site Name</label>
                            <input type="text" name="site_name" value="{{ old('site_name', $general['site_name']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', $general['timezone']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Locale</label>
                            <input type="text" name="locale" value="{{ old('locale', $general['locale']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Date Format</label>
                            <input type="text" name="date_format" value="{{ old('date_format', $general['date_format']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <p class="mt-1 text-[11px] text-slate-400">Examples: Y-m-d, d/m/Y.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Logo</label>
                            <input type="file" name="logo"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm file:text-sm file:mr-3 file:px-3 file:py-1.5 file:border-0 file:rounded-full file:bg-slate-900 file:text-slate-50 cursor-pointer">
                            @if($general['logo'])
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$general['logo']) }}"
                                         alt="Logo"
                                         class="h-10 object-contain">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Favicon</label>
                            <input type="file" name="favicon"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm file:text-sm file:mr-3 file:px-3 file:py-1.5 file:border-0 file:rounded-full file:bg-slate-900 file:text-slate-50 cursor-pointer">
                            @if($general['favicon'])
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$general['favicon']) }}"
                                         alt="Favicon"
                                         class="h-8 w-8 object-contain">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                                       bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                                       transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Save General
                        </button>
                    </div>
                </form>
            </section>

            {{-- Appearance --}}
            <section id="appearance"
                     class="bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Appearance</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Control the global color system and button behavior.
                        </p>
                    </div>
                    @if(session('status_appearance'))
                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                            {{ session('status_appearance') }}
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.settings.appearance.update') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                        @foreach(['primary_color','secondary_color','accent_color','background_color'] as $key)
                            <div>
                                <label class="block mb-1 text-[11px] font-medium text-slate-600">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="{{ $key }}_picker"
                                           value="{{ old($key, $appearance[$key]) }}"
                                           onchange="this.nextElementSibling.value = this.value"
                                           class="h-8 w-10 rounded-md border border-slate-200 cursor-pointer bg-white">
                                    <input type="text" name="{{ $key }}" value="{{ old($key, $appearance[$key]) }}"
                                           class="flex-1 rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                        @foreach(['card_color','border_color','text_primary','text_secondary'] as $key)
                            <div>
                                <label class="block mb-1 text-[11px] font-medium text-slate-600">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </label>
                                <input type="text" name="{{ $key }}" value="{{ old($key, $appearance[$key]) }}"
                                       class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block mb-1 text-[11px] font-medium text-slate-600">Button Radius</label>
                            <input type="text" name="button_radius" value="{{ old('button_radius', $appearance['button_radius']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-[11px] font-medium text-slate-600">Hover Scale</label>
                            <input type="text" name="hover_scale" value="{{ old('hover_scale', $appearance['hover_scale']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div class="flex items-end">
                            <div class="w-full">
                                <label class="block mb-1 text-[11px] font-medium text-slate-600">Preview</label>
                                <button type="button"
                                        class="px-3 py-1.5 rounded-full text-xs font-semibold cursor-pointer
                                               shadow-xl bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                                               transition transform duration-150 hover:scale-[var(--hover-scale)]">
                                    Button preview
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                                       bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                                       transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Save Appearance
                        </button>
                    </div>
                </form>
            </section>

            {{-- SEO --}}
            <section id="seo"
                     class="bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Global SEO</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Configure global metadata and robots configuration.
                        </p>
                    </div>
                    @if(session('status_seo'))
                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                            {{ session('status_seo') }}
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Site Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $seo['meta_title']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Canonical Base URL</label>
                            <input type="text" name="canonical_url" value="{{ old('canonical_url', $seo['canonical_url']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('meta_description', $seo['meta_description']) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Meta Keywords (comma separated)</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $seo['meta_keywords']) }}"
                               class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">OG Title</label>
                            <input type="text" name="og_title" value="{{ old('og_title', $seo['og_title']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">OG Description</label>
                            <input type="text" name="og_description" value="{{ old('og_description', $seo['og_description']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">OG Image URL</label>
                        <input type="text" name="og_image" value="{{ old('og_image', $seo['og_image']) }}"
                               class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Robots.txt</label>
                        <textarea name="robots_txt" rows="5"
                                  class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-mono text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('robots_txt', $seo['robots_txt']) }}</textarea>
                        <p class="mt-1 text-[11px] text-slate-400">
                            This content is served at /robots.txt.
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-2 text-xs">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="sitemap_enabled" value="0">
                            <input type="checkbox" name="sitemap_enabled" value="1"
                                   @checked(old('sitemap_enabled', $seo['sitemap_enabled']) ? true : false)
                                   class="rounded border-slate-300 bg-white text-sky-500 focus:ring-sky-500 cursor-pointer">
                            <span class="text-slate-600">Enable sitemap generation</span>
                        </div>
                        <button type="submit"
                                class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                                       bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                                       transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Save SEO
                        </button>
                    </div>
                </form>
            </section>

            {{-- SMTP --}}
            <section id="smtp"
                     class="bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">SMTP</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Manage email delivery credentials and send a test message.
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if(session('status_smtp'))
                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                                {{ session('status_smtp') }}
                            </span>
                        @endif
                        @if(session('status_smtp_test'))
                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                                {{ session('status_smtp_test') }}
                            </span>
                        @endif
                        @error('smtp_test')
                            <span class="text-xs text-rose-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Update SMTP --}}
                <form method="POST" action="{{ route('admin.settings.smtp.update') }}" class="space-y-4 mb-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Host</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $smtp['mail_host']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Port</label>
                            <input type="text" name="mail_port" value="{{ old('mail_port', $smtp['mail_port']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Username</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $smtp['mail_username']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Password</label>
                            <input type="password" name="mail_password"
                                   placeholder="Leave empty to keep existing"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Encryption</label>
                            <input type="text" name="mail_encryption" value="{{ old('mail_encryption', $smtp['mail_encryption']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">From Address</label>
                            <input type="text" name="mail_from_address" value="{{ old('mail_from_address', $smtp['mail_from_address']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">From Name</label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $smtp['mail_from_name']) }}"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 text-xs">
                        <button type="submit"
                                class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                                       bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                                       transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Save SMTP
                        </button>
                    </div>
                </form>

                {{-- Test SMTP --}}
                <div class="flex items-center justify-between pt-2 text-xs border-t border-slate-100 mt-4 pt-4">
                    <form method="POST" action="{{ route('admin.settings.smtp.test') }}" class="flex items-center gap-2 w-full md:w-auto">
                        @csrf
                        <input type="email" name="test_email" placeholder="Test email address"
                               class="w-full md:w-64 rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold shadow cursor-pointer
                                       bg-slate-900 text-slate-50 border border-slate-800
                                       transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Send Test
                        </button>
                    </form>
                </div>
            </section>

        </div>
    </div>
@endsection
