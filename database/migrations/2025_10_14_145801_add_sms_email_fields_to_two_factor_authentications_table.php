<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('two_factor_authentications', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('shared_secret');
            $table->timestamp('phone_number_verified_at')->nullable()->after('phone_number');
            $table->boolean('email_2fa_enabled')->default(false)->after('phone_number_verified_at');
            $table->string('email_2fa_code')->nullable()->after('email_2fa_enabled');
            $table->string('method')->default('totp')->after('email_2fa_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('two_factor_authentications', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('phone_number_verified_at');
            $table->dropColumn('email_2fa_enabled');
            $table->dropColumn('email_2fa_code');
            $table->dropColumn('method');
        });
    }
};