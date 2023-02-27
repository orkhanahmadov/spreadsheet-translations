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
        $translations = [
            'en' => ['login' => ['welcome' => 'Welcome']],
        ];

        $parser = $this->mock(SpreadsheetParser::class);
        $parser->shouldReceive('parse')->once()->withNoArgs()->andReturnSelf();
        $parser->shouldReceive('getTranslations')->once()->withNoArgs()->andReturn($translations);
        $generator = $this->mock(TranslationFileGenerator::class);
        $generator->shouldReceive('generate')->once()->withArgs(['en', $translations]);

        $this->artisan('translations:generate')
            ->expectsOutputToContain('Generating translation files for en...')
            ->expectsOutputToContain('Generated translation files for en!')
            ->assertExitCode(Command::SUCCESS);
    }
}
