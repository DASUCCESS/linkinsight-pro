<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            $table->dropUnique('linkedin_posts_linkedin_post_id_unique');
            $table->unique(['linkedin_profile_id', 'linkedin_post_id']);
        });
    }

    public function down(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            $table->dropUnique('linkedin_posts_linkedin_profile_id_linkedin_post_id_unique');
            $table->unique('linkedin_post_id');
        });
    }
};
