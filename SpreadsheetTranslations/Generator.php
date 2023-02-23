<?php

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Generator
{
    protected Collection $spreadsheetContents;

    public function __construct(protected Repository $config)
    {
    }

    public function handle(): void
    {
        $this->parseSpreadsheetContents();

//        foreach ($this->getLocales() as $localeIndex => $locale) {
//            foreach ($this->getTranslations() as $filename => $translations) {
//                $this->createTranslationFile(
//                    $locale,
//                    $filename,
//                    $this->generateTranslationFileContents($localeIndex, $translations)
//                );
//            }
//        }
    }

//    protected function createTranslationFile(string $locale, string $filename, string $contents): void
//    {
//        $this->createLocaleFolderIfMissing($locale);
//
//        file_put_contents(
//            lang_path("{$locale}/{$filename}.php"),
//            $contents
//        );
//    }
//
//    protected function createLocaleFolderIfMissing(string $locale): void
//    {
//        $folder = lang_path($locale);
//
//        if (file_exists($folder)) {
//            return;
//        }
//
//        mkdir($folder);
//    }

//    protected function generateTranslationFileContents(int $localeIndex, Collection $translations): string
//    {
//        $output = $translations
//            ->map(fn (Collection $translation, string $key) => "'$key' => '{$translation[$localeIndex]}'")
//            ->join(",\r\n");
//
//        return "<?php return [\r\n{$output}\r\n];";
//    }

    protected function getLocales(): Collection
    {
        return Collection::make($this->excelFileContents[0])->except([0])->values();
    }

//    protected function getRows(): Collection
//    {
//        return $this->excelFileContents->skip(1)->values();
//    }
//
//    protected function getTranslations(): Collection
//    {
//        return $this->getRows()->groupBy(fn (array $row) => Str::before($row[0], '.'))->map(
//            fn (Collection $rows) => $rows->mapWithKeys(
//                fn (array $row) => [Str::after($row[0], '.') => Collection::make($row)->except([0])->values()]
//            )
//        );
//    }

    protected function parseSpreadsheetContents(): void
    {
        $reader = $this->getReader()->load($this->getFilePath())->getActiveSheet();

        $this->getIgnoredColumns()->each(
            fn (int $column) => $reader->removeColumnByIndex($column)
        );

        $this->spreadsheetContents = Collection::make($reader->toArray());
    }

    protected function getReader(): BaseReader
    {
        return match ($this->config->get('spreadsheet-translations.type')) {
            'csv' => new Csv(),
            default => new Xlsx(),
        };
    }

    protected function getFilePath(): string
    {
        return $this->config->get('spreadsheet-translations.filepath');
    }

    protected function getIgnoredColumns(): Collection
    {
        return Collection::make($this->config->get('spreadsheet-translations.ignored_column_indexes'));
    }
}