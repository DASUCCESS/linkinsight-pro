<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('linkedin_posts', 'activity_category')) {
                $table->string('activity_category', 50)->nullable()->index(); // all, posts, comments, reactions, videos, images
            }
            if (!Schema::hasColumn('linkedin_posts', 'target_permalink')) {
                $table->text('target_permalink')->nullable();
            }
            if (!Schema::hasColumn('linkedin_posts', 'media_type')) {
                $table->string('media_type', 30)->nullable()->index(); // text, image, video, mixed
            }
        });
    }

    public function down(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            if (Schema::hasColumn('linkedin_posts', 'activity_category')) {
                $table->dropColumn('activity_category');
            }
            if (Schema::hasColumn('linkedin_posts', 'target_permalink')) {
                $table->dropColumn('target_permalink');
            }
            if (Schema::hasColumn('linkedin_posts', 'media_type')) {
                $table->dropColumn('media_type');
            }
        });
    }
};
