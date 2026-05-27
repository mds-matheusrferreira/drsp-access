<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'remember_token')) {
                $table->dropRememberToken();
            }

            if (Schema::hasColumn('users', 'password_hash')) {
                $table->dropColumn('password_hash');
            }
        });
    }
};
