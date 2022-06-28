<?php

namespace Nue\Setting\Providers;

use Illuminate\Support\ServiceProvider;
use Novay\Nue\Nue;
use Nue\Setting\Setting;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(Setting $extension)
    {
        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'nue-settings');
        }

        $this->app->booted(function () {
            Setting::routes(__DIR__.'/../../routes/web.php');
        });

        Nue::extend('settings', __CLASS__);
    }
}