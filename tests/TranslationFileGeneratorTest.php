<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class TranslationFileGeneratorTest extends TestCase
{
    public function testGeneratesTranslationFiles(): void
    {
        (new TranslationFileGenerator())->generate('en', $contents = [
            'identifier' => ['k' => 'v'],
        ]);

        $this->assertTrue(file_exists($filepath = lang_path('en.json')));
        $this->assertSame(
            json_encode($contents, JSON_PRETTY_PRINT),
            file_get_contents($filepath)
        );
    }
}
