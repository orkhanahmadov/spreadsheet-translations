<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;

class SpreadsheetParserTest extends TestCase
{
    protected SpreadsheetParser $parser;

    public function test(): void
    {
        $this->prepareSpreadsheetFile([
            ['key', 'comment', 'en', 'de'],
            ['login.welcome', 'welcome page', 'Welcome', 'Wilkommen'],
            ['login.form.first_name', 'form first name field', 'First name', 'Vorname'],
            ['dashboard.statistics', 'statistics title', 'Statistics', 'Statistik'],
        ]);

        $translations = $this->generator->parse();

        dd($translations);

        (new TranslationFileGenerator())->generate($translations);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->app->make(SpreadsheetParser::class);
    }
}
