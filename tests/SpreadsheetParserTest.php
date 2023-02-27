<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Support\Facades\Config;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetParserTest extends TestCase
{
    protected SpreadsheetParser $parser;

    public function testParsesListedLocaleTranslations(): void
    {
        $this->storeSpreadsheetFile([
            ['key', 'en'],
            ['login.welcome', 'Welcome'],
            ['dashboard.statistics', 'Statistics'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => ['welcome' => 'Welcome'],
                    'dashboard' => ['statistics' => 'Statistics'],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testParsesMultiDimensionalTranslations(): void
    {
        $this->storeSpreadsheetFile([
            ['key', 'en'],
            ['login.welcome', 'Welcome'],
            ['login.form.first_name', 'First name'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                        'form.first_name' => 'First name',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testIgnoresIrrelevantColumns(): void
    {
        $this->storeSpreadsheetFile([
            ['key', 'comment', 'en'],
            ['login.welcome', 'welcome page', 'Welcome'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testCanParseMultipleLocaleTranslations(): void
    {
        Config::set('spreadsheet-translations.locales', ['de', 'en']);
        $this->storeSpreadsheetFile([
            ['key', 'comment', 'en', 'de'],
            ['login.welcome', 'welcome page', 'Welcome', 'Wilkommen'],
            ['login.form.first_name', 'form first name field', 'First name', 'Vorname'],
            ['dashboard.statistics', 'statistics title', 'Statistics', 'Statistik'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                        'form.first_name' => 'First name',
                    ],
                    'dashboard' => ['statistics' => 'Statistics'],
                ],
                'de' => [
                    'login' => [
                        'welcome' => 'Wilkommen',
                        'form.first_name' => 'Vorname',
                    ],
                    'dashboard' => ['statistics' => 'Statistik'],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testCanChooseDifferentRowsAsHeader(): void
    {
        Config::set('spreadsheet-translations.header_row_number', 2);
        $this->storeSpreadsheetFile([
            ['login.welcome', 'welcome page', 'Welcome'],
            ['key', 'comment', 'en'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testCanChooseDifferentColumnForTranslationKey(): void
    {
        Config::set('spreadsheet-translations.key_column', 'B');
        $this->storeSpreadsheetFile([
            ['comment', 'key', 'en'],
            ['welcome page', 'login.welcome', 'Welcome'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testIgnoresRows(): void
    {
        Config::set('spreadsheet-translations.ignored_rows', [2]);
        $this->storeSpreadsheetFile([
            ['key', 'comment', 'en'],
            ['ignored', 'ignored', 'ignored'],
            ['login.welcome', 'welcome page', 'Welcome'],
        ]);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    public function testParsesFromDifferentSheetByName(): void
    {
        Config::set('spreadsheet-translations.filepath', self::TEST_FILE);
        Config::set('spreadsheet-translations.sheet', 'Named');
        $spreadsheet = new Spreadsheet();
        $named = new Worksheet(title: 'Named');
        $named->fromArray([
            ['key', 'comment', 'en'],
            ['login.welcome', 'welcome page', 'Welcome'],
        ]);
        $spreadsheet->addSheet($named);
        $spreadsheet->addSheet(new Worksheet(title: 'Another'), 0);
        $spreadsheet->setActiveSheetIndexByName('Another');

        $xlsx = new Xlsx($spreadsheet);
        $xlsx->save(self::TEST_FILE);

        $this->assertSame(
            [
                'en' => [
                    'login' => [
                        'welcome' => 'Welcome',
                    ],
                ],
            ],
            $this->generator->parse()->getTranslations()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('spreadsheet-translations.locales', ['en']);
        $this->generator = $this->app->make(SpreadsheetParser::class);
    }
}
