<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('channel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_edited')->default(false);
            $table->timestamps();

            $table->index('parent_message_id');
        });

        // Enforce that a message belongs to exactly one of channel_id / conversation_id.
        // Requires MySQL 8.0.16+ or PostgreSQL; drop this statement if targeting an older engine.
        DB::statement(
            'ALTER TABLE messages ADD CONSTRAINT chk_message_owner
             CHECK (
                (channel_id IS NOT NULL AND conversation_id IS NULL)
                OR (channel_id IS NULL AND conversation_id IS NOT NULL)
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
