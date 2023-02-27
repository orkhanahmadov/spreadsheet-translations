<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests\Commands;

use Illuminate\Support\Facades\Artisan;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;
use Orkhanahmadov\SpreadsheetTranslations\Tests\TestCase;
use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;
use Symfony\Component\Console\Command\Command;

class GenerateTranslationsCommandTest extends TestCase
{
    public function testRegistersCommand(): void
    {
        $this->assertArrayHasKey('translations:generate', Artisan::all());
    }

    public function testRunsParserAndFileGeneratorServices(): void
    {
        $parser = $this->mock(SpreadsheetParser::class);
        $parser->shouldReceive('parse')->once()->withNoArgs()->andReturnSelf();
        $parser->shouldReceive('getTranslations')->once()->withNoArgs()->andReturn(['whatever']);
        $generator = $this->mock(TranslationFileGenerator::class);
        $generator->shouldReceive('generate')->once()->with(['whatever']);

        $this->assertSame(Command::SUCCESS, Artisan::call('translations:generate'));
    }
}
