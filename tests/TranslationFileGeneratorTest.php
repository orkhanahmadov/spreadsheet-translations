<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class TranslationFileGeneratorTest extends TestCase
{
    public function testGeneratesTranslationFiles(): void
    {
        $generator = new TranslationFileGenerator();

        $generator->generate([
            'en' => [
                'file' => ['k' => 'v'],
            ],
        ]);

        $this->assertFileExists($filepath = lang_path('en/file.php'));
        $this->assertSame("<?php return [\r\n'k' => 'v'\r\n];", file_get_contents($filepath));
        unlink($filepath);
    }
}
