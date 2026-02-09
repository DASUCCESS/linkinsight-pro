<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('linkedin_post_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linkedin_post_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');

            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('reactions')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('reposts')->default(0);
            $table->unsignedInteger('saves')->default(0);

            $table->decimal('engagement_rate', 8, 4)->default(0);

            $table->timestamps();

            $table->unique(['linkedin_post_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_post_metrics');
    }
};
