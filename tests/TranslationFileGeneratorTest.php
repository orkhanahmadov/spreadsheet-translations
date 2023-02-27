<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class TranslationFileGeneratorTest extends TestCase
{
    protected TranslationFileGenerator $generator;

    public function testGeneratesTranslationFiles(): void
    {
        $this->assertFileDoesNotExist($filepath = lang_path('en/file.php'));

        $this->generator->generate([
            'en' => [
                'file' => ['k' => 'v'],
            ],
        ]);

        $this->assertFileExists($filepath);
        $this->assertSame("<?php return [\r\n'k' => 'v'\r\n];", file_get_contents($filepath));
        unlink($filepath);
    }

    public function testCreatesFolderWhenDoesNotExist(): void
    {
        $this->assertFileDoesNotExist($filepath = lang_path('fr'));

        $this->generator->generate(['fr' => []]);

        $this->assertFileExists($filepath);
        rmdir($filepath);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new TranslationFileGenerator();
    }
}
