<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasLastLogin = DB::selectOne("SHOW COLUMNS FROM `users` LIKE 'last_login_at'");
        if (! $hasLastLogin) {
            DB::statement('ALTER TABLE `users` ADD COLUMN `last_login_at` TIMESTAMP NULL DEFAULT NULL AFTER `remember_token`');
        }

        if (! Schema::hasTable('login_activities')) {
            Schema::create('login_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('logged_in_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_activities');

        $hasLastLogin = DB::selectOne("SHOW COLUMNS FROM `users` LIKE 'last_login_at'");
        if ($hasLastLogin) {
            DB::statement('ALTER TABLE `users` DROP COLUMN `last_login_at`');
        }
    }
};
