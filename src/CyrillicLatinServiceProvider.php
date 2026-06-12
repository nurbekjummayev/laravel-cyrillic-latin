<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin;

use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CyrillicLatinServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-cyrillic-latin')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Transliterator::class, function ($app) {
            $config = $app['config']->get('cyrillic-latin', []);

            return new Transliterator(
                $config['dictionaries']['latin'] ?? [],
                $config['dictionaries']['cyrillic'] ?? [],
                $config['use_default_dictionary'] ?? true,
            );
        });

        $this->app->alias(Transliterator::class, 'transliterator');
    }

    public function packageBooted(): void
    {
        if (! Str::hasMacro('toLatin')) {
            Str::macro('toLatin', fn (string $text): string => app(Transliterator::class)->toLatin($text));
        }

        if (! Str::hasMacro('toCyrillic')) {
            Str::macro('toCyrillic', fn (string $text): string => app(Transliterator::class)->toCyrillic($text));
        }
    }
}
