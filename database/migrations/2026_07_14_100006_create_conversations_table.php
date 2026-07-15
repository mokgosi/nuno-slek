<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Stored as a string column and cast to a PHP enum (App\Enums\ConversationType) on the model,
            // consistent with using enums over raw constants for domain values.
            $table->string('type'); // 'dm' | 'group_dm'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
