<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class TranslationFileGeneratorTest extends TestCase
{
    public function testGeneratesTranslationFiles(): void
    {
        $filepath = lang_path('en.json');

        $this->assertFalse(file_exists($filepath));

        (new TranslationFileGenerator())->generate('en', [
            'identifier' => ['k' => 'v'],
        ]);

        $this->assertTrue(file_exists($filepath));
        $this->assertSame(
            '{"identifier":{"k":"v"}}',
            file_get_contents($filepath)
        );

        unlink($filepath);
    }
}
