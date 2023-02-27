<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Support\Facades\Config;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetTranslationsServiceProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected const TEST_FILE = __DIR__ . '/file.xlsx';

    protected function getPackageProviders($app)
    {
        return [
            SpreadsheetTranslationsServiceProvider::class,
        ];
    }

    protected function storeSpreadsheetFile(array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getWorksheetIterator()->current();
        $worksheet->fromArray(source: $rows, strictNullComparison: true);

        $xlsx = new Xlsx($spreadsheet);
        $xlsx->save(self::TEST_FILE);

        Config::set('spreadsheet-translations.filepath', self::TEST_FILE);
    }

    protected function tearDown(): void
    {
        if (file_exists(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }

        parent::tearDown();
    }
}
