<?php

namespace SouthCN\EasyUC;

use SouthCN\EasyUC\Console\SyncSitesCommand;
use SouthCN\EasyUC\Console\SyncUsersCommand;
use SouthCN\EasyUC\Exceptions\ConfigUndefinedException;

class EasyUCServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config.php', 'easyuc'
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        $this->publishes([
            __DIR__ . '/../config.php' => config_path('easyuc.php'),
        ]);

        $this->checkConfig();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncUsersCommand::class,
                SyncSitesCommand::class,
            ]);
        }
    }

    protected function checkConfig(): void
    {
        collect([
            'easyuc.app' => 'UC_APP',
            'easyuc.ticket' => 'UC_TICKET',
            'easyuc.site_app_id' => 'UC_SITE_APP_ID',
            'easyuc.route.logout' => 'UC_ROUTE_LOGOUT',
            'easyuc.oauth.ip' => 'UC_OAUTH_TRUSTED_IP',
            'easyuc.oauth.base_url' => 'UC_BASE_URL',
        ])->each(function (string $env, string $config) {
            if (!config($config)) {
                throw new ConfigUndefinedException("请配置$env");
            }
        });
    }
}
