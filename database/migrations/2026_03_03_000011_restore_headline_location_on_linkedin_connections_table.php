<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('linkedin_connections', function (Blueprint $table) {
            if (! Schema::hasColumn('linkedin_connections', 'headline')) {
                $table->string('headline', 255)->nullable()->after('full_name');
            }

            if (! Schema::hasColumn('linkedin_connections', 'location')) {
                $table->string('location', 191)->nullable()->after('headline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('linkedin_connections', function (Blueprint $table) {
            if (Schema::hasColumn('linkedin_connections', 'headline')) {
                $table->dropColumn('headline');
            }

            if (Schema::hasColumn('linkedin_connections', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};
