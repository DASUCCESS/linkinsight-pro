<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->unique();
            $table->string('buyer')->nullable();
            $table->string('email')->nullable();
            $table->string('domain')->nullable();
            $table->string('item_id')->nullable();
            $table->string('license_token')->nullable();
            $table->enum('status', ['active', 'inactive', 'revoked', 'expired'])->default('inactive');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('support_ends_at')->nullable();
            $table->boolean('is_owner_license')->default(false); // owner auto activation bypass
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
