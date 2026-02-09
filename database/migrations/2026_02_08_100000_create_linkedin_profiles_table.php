<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('linkedin_id')->nullable();      // public id / URN
            $table->string('public_url')->unique();
            $table->string('name');
            $table->string('headline')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('location')->nullable();
            $table->string('industry')->nullable();

            $table->unsignedInteger('connections_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);

            $table->enum('profile_type', ['own', 'competitor', 'peer'])->default('own');
            $table->boolean('is_primary')->default(false);

            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->default('idle');     // idle, running, ok, error
            $table->text('sync_error')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'profile_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_profiles');
    }
};
