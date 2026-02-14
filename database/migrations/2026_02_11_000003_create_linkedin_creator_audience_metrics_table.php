<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_creator_audience_metrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('linkedin_profile_id')->constrained('linkedin_profiles')->cascadeOnDelete();
            $table->date('metric_date');

            /*
              Flexible payload for creator audience analytics:
              {
                "unique_viewers": 123,
                "follower_gains": 10,
                "profile_visits": 55,
                "audience_breakdown": {...},
                "top_locations": [...],
                "top_industries": [...]
              }
            */
            $table->json('metrics')->nullable();

            $table->string('source_hash', 64)->nullable()->index();
            $table->timestamps();

            $table->unique(['linkedin_profile_id', 'metric_date'], 'li_creator_aud_profile_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_creator_audience_metrics');
    }
};
