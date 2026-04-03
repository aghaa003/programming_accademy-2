<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - Upgrades ai_messages.content from TEXT (64 KB max on MySQL/MariaDB)
     *   to MEDIUMTEXT (16 MB) so large AI responses + code blocks never truncate.
     *
     * - Replaces the single-column index on conversation_id with a composite
     *   (conversation_id, created_at) index.  The sendMessage query always does
     *   WHERE conversation_id = ? ORDER BY created_at DESC LIMIT 30, so the
     *   composite index lets MySQL/MariaDB satisfy both the filter and the sort
     *   in one index scan without a filesort.  The leftmost-prefix rule means
     *   it still covers any query that filters on conversation_id alone.
     *
     * SQLite (used in tests) stores all text as TEXT regardless of declared type
     * and handles both index shapes natively, so ALTER TABLE is skipped there.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ai_messages MODIFY COLUMN content MEDIUMTEXT NOT NULL');

            // Must drop the FK before dropping the index it depends on,
            // then swap the index, then re-add the FK.
            DB::statement('ALTER TABLE ai_messages DROP FOREIGN KEY ai_messages_conversation_id_foreign');

            Schema::table('ai_messages', function (Blueprint $table) {
                $table->dropIndex('ai_messages_conversation_id_index');
                $table->index(['conversation_id', 'created_at'], 'ai_messages_conv_created_at_idx');
            });

            // The new composite index has conversation_id as its leftmost
            // prefix, so MySQL can use it to enforce the FK.
            DB::statement('ALTER TABLE ai_messages ADD CONSTRAINT ai_messages_conversation_id_foreign
                FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE');
        } else {
            // SQLite: no MODIFY COLUMN needed (TEXT is unlimited), but we
            // still swap the index.
            Schema::table('ai_messages', function (Blueprint $table) {
                if (Schema::hasIndex('ai_messages', 'ai_messages_conversation_id_index')) {
                    $table->dropIndex('ai_messages_conversation_id_index');
                }
                $table->index(['conversation_id', 'created_at'], 'ai_messages_conv_created_at_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropIndex('ai_messages_conv_created_at_idx');
            $table->index('conversation_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ai_messages MODIFY COLUMN content TEXT NOT NULL');
        }
    }
};
