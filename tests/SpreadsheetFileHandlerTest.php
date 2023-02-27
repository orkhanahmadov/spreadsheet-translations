<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetFileHandler;

class SpreadsheetFileHandlerTest extends TestCase
{
    public function testLocalFilePath(): void
    {
        Config::set('spreadsheet-translations.filepath', $path = __DIR__ . 'locale_file_path');

        $this->assertSame($path, $this->app->make(SpreadsheetFileHandler::class)->getFilePath());
    }

    public function testDownloadsRemoteFileWhenFilePathIsUrl(): void
    {
        Config::set('spreadsheet-translations.filepath', $url = 'https://remote-file.com/xlsx');
        Http::fake([$url => Http::response($contents = 'remote file contents')]);

        $path = $this->app->make(SpreadsheetFileHandler::class)->getFilePath();

        $this->assertSame($contents, file_get_contents($path));
        Http::assertSent(fn (Request $request) => $request->url() === $url);
    }
}
