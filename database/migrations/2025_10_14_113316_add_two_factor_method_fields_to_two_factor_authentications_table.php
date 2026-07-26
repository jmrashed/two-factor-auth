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
            $table->string('method')->default('totp')->after('shared_secret');
            $table->string('phone_number')->nullable()->after('method');
            $table->timestamp('phone_number_verified_at')->nullable()->after('phone_number');
            $table->boolean('email_2fa_enabled')->default(false)->after('phone_number_verified_at');
            $table->string('email_2fa_code')->nullable()->after('email_2fa_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('two_factor_authentications', function (Blueprint $table) {
            $table->dropColumn(['method', 'phone_number', 'phone_number_verified_at', 'email_2fa_enabled', 'email_2fa_code']);
        });
    }
};
