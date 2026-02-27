<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('log-hole.database.table', 'logs_hole'), function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->text('message');
            $table->json('context')->nullable();
            $table->dateTime('logged_at')->nullable();

            $table->index('level');
            $table->index('logged_at');
            $table->index(['level', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('log-hole.database.table', 'logs_hole'));
    }
};
