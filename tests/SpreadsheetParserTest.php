<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Support\Facades\Config;
use Orkhanahmadov\SpreadsheetTranslations\FileGenerator;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        (new FileGenerator())->generate($translations);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->app->make(SpreadsheetParser::class);
    }
}
