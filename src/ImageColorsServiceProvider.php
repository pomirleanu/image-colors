<?php

namespace Pomirleanu\ImageColors;

use Illuminate\Support\ServiceProvider;

class ImageColorsServiceProvider extends ServiceProvider
{

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../config/image-colors.php' => config_path('image-colors.php'),
        ]);
        $this->app->singleton(ImageColors::class, function ($app) {
            return new ImageColors($app);
        });
        $this->mergeConfigFrom(
            __DIR__ . '/../config/image-colors.php', 'image-colors'
        );
    }
}
