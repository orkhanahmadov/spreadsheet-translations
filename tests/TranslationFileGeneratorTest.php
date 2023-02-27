<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Tests;

use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class TranslationFileGeneratorTest extends TestCase
{
    protected TranslationFileGenerator $generator;
    protected string $filepath;

    public function testGeneratesTranslationFiles(): void
    {
        $this->assertFileDoesNotExist($this->filepath);

        $this->generator->generate('en', [
            'file' => ['k' => 'v'],
        ]);

        $this->assertFileExists($this->filepath);
        $this->assertSame(
            "<?php return [\r\n'k' => 'v'\r\n];",
            file_get_contents($this->filepath)
        );
    }

    public function testEscapesSingleQuiteCharacters(): void
    {
        $this->generator->generate('en', [
            'file' => ['k' => "don't do it!"],
        ]);

        $this->assertSame(
            "<?php return [\r\n'k' => 'don\'t do it!'\r\n];",
            file_get_contents($this->filepath)
        );
    }

    public function testCreatesFolderWhenDoesNotExist(): void
    {
        $this->assertFileDoesNotExist($filepath = lang_path('fr'));

        $this->generator->generate('fr', []);

        $this->assertFileExists($filepath);
        rmdir($filepath);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new TranslationFileGenerator();
        $this->filepath = lang_path('en/file.php');
    }

    protected function tearDown(): void
    {
        if (isset($this->filepath) && file_exists($this->filepath)) {
            unlink($this->filepath);
        }

        parent::tearDown();
    }
}
