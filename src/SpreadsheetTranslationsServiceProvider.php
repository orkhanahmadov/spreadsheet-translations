<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Support\ServiceProvider;
use Orkhanahmadov\SpreadsheetTranslations\Commands\GenerateTranslationsCommand;

class SpreadsheetTranslationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('spreadsheet-translations.php'),
            ], 'config');

            $this->commands([
                GenerateTranslationsCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'spreadsheet-translations');
    }
}
