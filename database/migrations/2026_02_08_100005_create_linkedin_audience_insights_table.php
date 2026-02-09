<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_audience_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linkedin_profile_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');

            $table->json('top_job_titles')->nullable();   // [{label, percentage}, ...]
            $table->json('top_industries')->nullable();
            $table->json('top_locations')->nullable();
            $table->json('engagement_sources')->nullable();

            $table->timestamps();

            $table->unique(
                ['linkedin_profile_id', 'snapshot_date'],
                'audience_profile_date_unique'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_audience_insights');
    }
};
