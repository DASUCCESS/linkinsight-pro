<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('linkedin_post_metrics', function (Blueprint $table) {
            if (!Schema::hasColumn('linkedin_post_metrics', 'unique_impressions')) {
                $table->unsignedInteger('unique_impressions')->default(0)->after('impressions');
            }
            if (!Schema::hasColumn('linkedin_post_metrics', 'video_views')) {
                $table->unsignedInteger('video_views')->default(0)->after('saves');
            }
            if (!Schema::hasColumn('linkedin_post_metrics', 'follows_from_post')) {
                $table->unsignedInteger('follows_from_post')->default(0)->after('video_views');
            }
            if (!Schema::hasColumn('linkedin_post_metrics', 'profile_visits_from_post')) {
                $table->unsignedInteger('profile_visits_from_post')->default(0)->after('follows_from_post');
            }
        });
    }

    public function down(): void
    {
        Schema::table('linkedin_post_metrics', function (Blueprint $table) {
            foreach (['unique_impressions','video_views','follows_from_post','profile_visits_from_post'] as $col) {
                if (Schema::hasColumn('linkedin_post_metrics', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
