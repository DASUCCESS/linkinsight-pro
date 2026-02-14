<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_connections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('linkedin_profile_id')
                ->constrained('linkedin_profiles')
                ->cascadeOnDelete();

            // Best-effort stable identifiers from LinkedIn
            $table->string('linkedin_connection_id', 191)->nullable(); // e.g. urn/id when available
            $table->string('public_identifier', 191)->nullable();      // e.g. /in/{handle}/
            $table->string('profile_url', 2048)->nullable();

            $table->string('full_name', 191)->nullable();
            $table->string('headline', 255)->nullable();
            $table->string('location', 191)->nullable();
            $table->string('industry', 191)->nullable();
            $table->string('profile_image_url', 2048)->nullable();

            $table->unsignedTinyInteger('degree')->nullable(); // 1,2,3
            $table->unsignedInteger('mutual_connections_count')->default(0);

            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            // Dedupe/supporting fingerprint
            $table->string('dedupe_key', 191)->index('li_conn_dedupe_idx'); // computed in code
            $table->string('source_hash', 64)->nullable()->index('li_conn_sourcehash_idx'); // sha256

            $table->timestamps();

            // Constraints / indexes (explicit short names to satisfy MySQL 64-char limit)
            $table->unique(
                ['linkedin_profile_id', 'dedupe_key'],
                'li_conn_profile_dedupe_unique'
            );

            $table->index(
                ['linkedin_profile_id', 'public_identifier'],
                'li_conn_profile_public_idx'
            );

            $table->index(
                ['linkedin_profile_id', 'linkedin_connection_id'],
                'li_conn_profile_lcid_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_connections');
    }
};
