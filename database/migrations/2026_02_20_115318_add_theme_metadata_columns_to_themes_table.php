<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // Basic file related info
            $table->string('path')->nullable()->after('slug');          // e.g. "themes/default"
            $table->string('author')->nullable()->after('path');
            $table->string('screenshot')->nullable()->after('author');

            // Installation state
            $table->boolean('is_installed')->default(true)->after('is_active');
        });

        // Optional: mark existing themes as installed
        DB::table('themes')->update([
            'is_installed' => true,
        ]);

        // Optional: if you already have a "default" theme row, set its path
        DB::table('themes')
            ->where('slug', 'default')
            ->update([
                'path' => 'themes/default',
            ]);
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn(['path', 'author', 'screenshot', 'is_installed']);
        });
    }
};
