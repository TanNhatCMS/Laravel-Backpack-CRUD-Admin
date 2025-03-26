<?php

namespace Backpack\CRUD\app\Console\Commands\Themes;

use Illuminate\Console\Command;

class RequireThemeTabler extends Command
{
    use InstallsTheme;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:require:theme-tabler
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Tabler theme';

    /**
     * Backpack addons install attribute.
     *
     * @var array
     */
    public static $addon = [
        'name' => 'Tabler <fg=green>(default)</>',
        'description' => [
            'UI provided by Tabler, a Bootstrap 5 template. Lots of new features, including a dark mode.',
            '<fg=blue>https://github.com/laravel-backpack/theme-tabler/</>',
        ],
        'repo' => 'tannhatcms/theme-tabler',
        'path' => 'vendor/tannhatcms/theme-tabler',
        'command' => 'backpack:require:theme-tabler',
        'view_namespace' => 'tannhatcms.theme-tabler::',
        'publish_tag' => 'theme-tabler-config',
        'provider' => '\Backpack\ThemeTabler\AddonServiceProvider',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->installTheme();
    }
}
