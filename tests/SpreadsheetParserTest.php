<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Support\Facades\Config;
use Orkhanahmadov\SpreadsheetTranslations\FileGenerator;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;

class SpreadsheetParserTest extends TestCase
{
    protected SpreadsheetParser $parser;

    public function test(): void
    {
        Config::set('spreadsheet-translations.filepath', __DIR__ . '/files/test.xlsx');

        $translations = $this->generator->parse()->getTranslations();

        (new FileGenerator())->generate($translations);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->app->make(SpreadsheetParser::class);
    }
}
