<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linkedin_profile_id')->constrained()->cascadeOnDelete();

            $table->string('linkedin_post_id')->unique();
            $table->string('permalink')->nullable();
            $table->dateTime('posted_at');

            $table->string('post_type')->default('text'); // text, image, video, article, document, poll, etc
            $table->boolean('is_reshare')->default(false);
            $table->boolean('is_sponsored')->default(false);

            $table->text('content_excerpt')->nullable();

            $table->timestamps();

            $table->index(['linkedin_profile_id', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_posts');
    }
};
