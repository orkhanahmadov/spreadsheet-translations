<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Config;
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

        $http = $this->mock(Factory::class);

        if ($this->laravelVersion() > 8) {
            $http->shouldReceive('throw')->once()->withNoArgs();
        }
        $http->shouldReceive('get')->once()->with($url)->andReturnSelf();
        $http->shouldReceive('body')->once()->withNoArgs()->andReturn($contents = 'remote file contents');

        $path = $this->app->make(SpreadsheetFileHandler::class)->getFilePath();

        $this->assertSame($contents, file_get_contents($path));
    }
}
