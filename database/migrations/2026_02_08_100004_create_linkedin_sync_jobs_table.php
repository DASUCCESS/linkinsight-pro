<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linkedin_profile_id')->nullable()->constrained()->nullOnDelete();

            $table->string('source')->default('extension'); // extension, backend, cron
            $table->string('type')->default('full');        // full, profile, posts
            $table->string('status')->default('pending');   // pending, running, success, failed
            $table->text('error_message')->nullable();

            $table->json('payload')->nullable();            // meta about what was synced

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_sync_jobs');
    }
};
