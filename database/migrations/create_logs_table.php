<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('log-hole.database.table'), function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->text('message');
            $table->json('context')->nullable();
            $table->dateTime('logged_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('log-hole.database.table'));
    }
};
