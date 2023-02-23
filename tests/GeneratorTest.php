<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Support\Facades\Config;
use Orkhanahmadov\SpreadsheetTranslations\Generator;

class GeneratorTest extends TestCase
{
    protected Generator $generator;

    public function test(): void
    {
        Config::set('spreadsheet-translations.filepath', __DIR__ . '/files/test.xlsx');

        $this->generator->handle();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->app->make(Generator::class);
    }
}
