<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Laragear\MetaModel\CustomizableMigration;

/**
 * @internal
 */
class TwoFactorAuthenticationMigration extends CustomizableMigration
{
    /**
     * Create the table columns.
     */
    public function create(Blueprint $table): void
    {
        $table->id();

        $this->createMorph($table, 'authenticatable', 'two_factor_authenticatable_index');

        $table->text('shared_secret');
        $table->timestampTz('enabled_at')->nullable();
        $table->string('label');
        $table->unsignedTinyInteger('digits')->default(6);
        $table->unsignedTinyInteger('seconds')->default(30);
        $table->unsignedTinyInteger('window')->default(0);
        $table->string('algorithm', 16)->default('sha1');
        $table->text('recovery_codes')->nullable();
        $table->timestampTz('recovery_codes_generated_at')->nullable();
        $table->json('safe_devices')->nullable();

        $this->addColumns($table);

        $table->timestampsTz();
    }
}
