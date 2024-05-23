<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor two factor authentication]
 */

use Illuminate\Database\Schema\Blueprint;
use Jmrashed\TwoFactorAuth\Models\TwoFactorAuthentication;

return TwoFactorAuthentication::migration(function (Blueprint $table) {
    // Here you can add custom columns to the Two Factor table.
    //
    // $table->string('alias')->nullable();
});
