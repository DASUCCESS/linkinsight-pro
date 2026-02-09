<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_profile_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linkedin_profile_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');

            $table->unsignedInteger('connections_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('profile_views')->default(0);
            $table->unsignedInteger('search_appearances')->default(0);

            $table->unsignedInteger('posts_count')->default(0);
            $table->unsignedInteger('impressions_7d')->default(0);
            $table->unsignedInteger('engagements_7d')->default(0);

            $table->timestamps();

            $table->unique(['linkedin_profile_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_profile_metrics');
    }
};
