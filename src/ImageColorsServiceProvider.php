<?php

namespace Pomirleanu\ImageColors;

use Illuminate\Support\ServiceProvider;

class ImageColorsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/image-colors.php' => config_path('image-colors.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('image.colors', function () {
            $config = config('image-colors');

            return new ImageColors($config);
        });

        $this->app->alias('ImageColors', 'Pomirleanu\ImageColors\ImageColors');
        $this->mergeConfigFrom(
            __DIR__ . '/../config/image-colors.php', 'image-colors'
        );
    }
}
