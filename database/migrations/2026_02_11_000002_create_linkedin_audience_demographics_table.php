<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_audience_demographics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('linkedin_profile_id')->constrained('linkedin_profiles')->cascadeOnDelete();
            $table->date('snapshot_date');

            /*
              Expected structure (flexible):
              {
                "job_titles": [{"label":"Software Engineer","count":123}, ...],
                "industries": [{"label":"IT Services","count":99}, ...],
                "locations":  [{"label":"Lagos","count":50}, ...],
                "seniority":  [{"label":"Senior","count":10}, ...],
                "company_size":[...],
                "age_range":[...],
                "gender":[...]
              }
            */
            $table->json('demographics')->nullable();

            // Optional: if extension sends followers_count at snapshot time
            $table->unsignedInteger('followers_count')->default(0);

            $table->string('source_hash', 64)->nullable()->index(); // sha256 of demographics JSON
            $table->timestamps();

            $table->unique(['linkedin_profile_id', 'snapshot_date'], 'li_demo_profile_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_audience_demographics');
    }
};
