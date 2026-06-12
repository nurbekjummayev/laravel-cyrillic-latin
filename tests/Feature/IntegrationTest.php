<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Tests\Feature;

use Illuminate\Support\Str;
use NurbekJummayev\LaravelCyrillicLatin\CyrillicLatinServiceProvider;
use NurbekJummayev\LaravelCyrillicLatin\Facades\Transliterator as TransliteratorFacade;
use NurbekJummayev\LaravelCyrillicLatin\Transliterator;
use Orchestra\Testbench\TestCase;

class IntegrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CyrillicLatinServiceProvider::class];
    }

    public function test_container_resolves_singleton(): void
    {
        $this->assertInstanceOf(Transliterator::class, $this->app->make('transliterator'));
        $this->assertSame($this->app->make('transliterator'), $this->app->make(Transliterator::class));
    }

    public function test_facade_converts(): void
    {
        $this->assertSame('Salom', TransliteratorFacade::toLatin('Салом'));
        $this->assertSame('Салом', TransliteratorFacade::toCyrillic('Salom'));
    }

    public function test_str_macros(): void
    {
        $this->assertSame('Salom', Str::toLatin('Салом'));
        $this->assertSame('Салом', Str::toCyrillic('Salom'));
    }

    public function test_config_dictionary_is_used(): void
    {
        $this->app['config']->set('cyrillic-latin.dictionaries.latin', ['цех' => 'sex']);
        $this->app->forgetInstance(Transliterator::class);

        $this->assertSame('sex', $this->app->make(Transliterator::class)->toLatin('цех'));
    }
}
