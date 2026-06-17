<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table): void {
                $table->integer('id', true);
                $table->text('log');
                $table->text('user');
                $table->dateTime('date_created');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
