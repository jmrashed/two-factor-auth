# Migration

When integrating the Two-Factor Authentication package into your project, managing migrations is simplified for ease of customization.

## Migration Setup

The migration process is straightforward. After installing the package, navigate to the `database/migrations` directory. You'll find a migration file resembling the following:

```php
use Jmrashed\TwoFactorAuth\Models\User;

return User::migration();
```

Despite its simplicity, this migration setup is fully functional. However, it's designed to be easily customizable to suit your specific needs.

## Adding Columns

To include additional columns in the migration, you can leverage a callback within the `migration()` method. This callback receives a table blueprint as a parameter, allowing you to define your desired schema.

```php
use Illuminate\Database\Schema\Blueprint;
use Jmrashed\TwoFactorAuth\Models\User;

return User::migration(function (Blueprint $table) {
    $table->boolean('is_admin')->default(false);
    $table->string('phone_number')->nullable();
});
```

This flexibility enables you to extend the default migration schema according to your application requirements.

## Executing Logic After Up & Before Down

Sometimes, you might need to execute specific logic after the table creation or before dropping it. This can be achieved using the `afterUp()` and `beforeDown()` methods, respectively.

```php
use Illuminate\Database\Schema\Blueprint;
use Jmrashed\TwoFactorAuth\Models\User;

return User::migration()
    ->afterUp(function (Blueprint $table) {
        $table->foreignId('organization_id')->constrained('organizations');
    })
    ->beforeDown(function (Blueprint $table) {
        $table->dropForeign(['organization_id']);
    });
```

These methods provide hooks for executing additional operations, ensuring seamless integration with your application's database schema.

## Morphs Configuration

The migration also supports configuring the morph type for relations. You can specify the morph type as `numeric`, `uuid`, or `ulid`.

```php
use Jmrashed\TwoFactorAuth\Models\User;

return User::migration()->morphUuid;
```

This customization ensures compatibility with different morph types based on your application's requirements.

## Custom Table Name

If you prefer a custom table name instead of the default, you can specify it using the `$useTable` static property of the User model.

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Jmrashed\TwoFactorAuth\Models\User as TwoFactorAuthUser;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        TwoFactorAuthUser::$useTable = 'custom_users';
    }
}
```

This allows you to tailor the table name to better fit your application's conventions.

## Configuring the User Model

Additionally, you can configure the User model's fillable, guarded, hidden, visible, and appended attributes using static properties. These properties are merged with the model's original configuration, providing a flexible customization approach.

```php
use Illuminate\Database\Eloquent\Casts\AsEncrypted;
use Jmrashed\TwoFactorAuth\Models\User as TwoFactorAuthUser;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        TwoFactorAuthUser::$useCasts = [
            'phone_number' => AsEncrypted::class,
        ];
        TwoFactorAuthUser::$useHidden = ['phone_number'];
    }
}
```

This advanced customization enables fine-grained control over how user attributes are managed within the application.
