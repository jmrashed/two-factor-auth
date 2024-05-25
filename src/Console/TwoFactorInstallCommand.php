<?php
/**
 * @author [jmrashed]
 * @email [jmrashed@mail.com]
 * @create date 2024-05-23 13:59:42
 * @modify date 2024-05-23 13:59:42
 * @desc [ two-factor ]
 */

namespace Jmrashed\TwoFactorAuth\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleContract;
use Jmrashed\TwoFactorAuth\TwoFactorServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @internal
 */
class TwoFactorInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'two-factor-auth:install {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Two Factor package files';

    /**
     * Execute the console command.
     */
    public function handle(ConsoleContract $console): void
    {
        foreach (['migrations', 'config', 'views', 'translations'] as $tag) {
            $console->call('vendor:publish', [
                '--provider' => TwoFactorServiceProvider::class,
                '--force' => $this->option('force'),
                '--tag' => $tag,
            ]);
        }
    }
}
