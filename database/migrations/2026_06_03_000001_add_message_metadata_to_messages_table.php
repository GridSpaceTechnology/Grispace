<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->string('attachment_path')->nullable()->after('message');
            $table->string('attachment_type')->nullable()->after('attachment_path');
            $table->string('attachment_name')->nullable()->after('attachment_type');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_name');

            $table->index(['sender_type']);
            $table->index(['is_read', 'conversation_id']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_type']);
            $table->dropIndex(['is_read', 'conversation_id']);
            $table->dropColumn([
                'sender_type',
                'attachment_path',
                'attachment_type',
                'attachment_name',
                'attachment_size',
            ]);
        });
    }
};
