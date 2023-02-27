<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Support\Collection;

class TranslationFileGenerator
{
    public function generate(string $locale, array $translationGroup): void
    {
        $this->createLocaleFolderIsMissing($locale);

        foreach ($translationGroup as $filename => $translations) {
            file_put_contents(
                lang_path("{$locale}/{$filename}.php"),
                $this->generateTranslationFileContents($translations)
            );
        }
    }

    protected function generateTranslationFileContents(array $translations): string
    {
        $output = Collection::make($translations)
            ->map(fn (?string $translation) => str_replace("'", "\'", $translation ?? ''))
            ->map(fn (?string $translation, string $key) => "'$key' => '{$translation}'")
            ->join(",\r\n");

        return "<?php return [\r\n{$output}\r\n];";
    }

    protected function createLocaleFolderIsMissing(string $locale): void
    {
        $folder = lang_path($locale);

        if (file_exists($folder)) {
            return;
        }

        mkdir($folder);
    }
}
