<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Generator
{
    protected Worksheet $worksheet;
    protected array $localeColumns = [];
    protected array $translations = [];

    public function __construct(protected Repository $config)
    {
    }

    protected function getHeaderRow(): Row
    {
        return $this->worksheet
            ->getRowIterator()
            ->seek($this->getHeaderRowNumber())
            ->current();
    }

    public function handle(): void
    {
        $this->worksheet = $this->getReader()
            ->load($this->config->get('spreadsheet-translations.filepath'))
            ->getActiveSheet();

        foreach ($this->getHeaderRow()->getCellIterator() as $cell) {
            if (! $this->getEnabledLocales()->contains($cell->getValue())) {
                continue;
            }

            $this->localeColumns[$cell->getValue()] = $cell->getColumn();
        }

        foreach ($this->worksheet->getRowIterator() as $row) {
            if ($row->getRowIndex() === $this->getHeaderRowNumber()) {
                continue;
            }

            if (in_array($row->getRowIndex(), $this->config->get('spreadsheet-translations.ignored_rows'))) {
                continue;
            }

            [$filename, $identifier] = $this->parseTranslationKey(
                $row->getColumnIterator()->seek($this->getKeyColumn())->current()->getValue()
            );

            foreach ($this->localeColumns as $locale => $localeColumn) {
                if (! isset($this->translations[$locale])) {
                    $this->translations[$locale] = [];
                }

                if (! isset($this->translations[$locale][$filename])) {
                    $this->translations[$locale][$filename] = [];
                }

                $this->translations[$locale][$filename][$identifier] = $row->getColumnIterator()->seek($localeColumn)->current()->getValue();
            }
        }

        $this->generateTranslationFiles();
    }

    protected function generateTranslationFiles(): void
    {
        foreach ($this->translations as $locale => $localeGroup) {
            $this->createLocaleFolder($locale);

            foreach ($localeGroup as $filename => $translations) {
                file_put_contents(
                    lang_path("{$locale}/{$filename}.php"),
                    $this->generateTranslationFileContents($translations)
                );
            }
        }
    }

    protected function generateTranslationFileContents(array $translations): string
    {
        $output = Collection::make($translations)
            ->map(fn (string $translation, string $key) => "'$key' => '{$translation}'")
            ->join(",\r\n");

        return "<?php return [\r\n{$output}\r\n];";
    }

    protected function createLocaleFolder(string $locale): void
    {
        $folder = lang_path($locale);

        if (file_exists($folder)) {
            return;
        }

        mkdir($folder);
    }

    protected function parseTranslationKey(string $key): array
    {
        return [
            Str::before($key, '.'), // filename
            Str::after($key, '.'), // identifier
        ];
    }

    protected function getReader(): BaseReader
    {
        return match ($this->config->get('spreadsheet-translations.type')) {
            'csv' => new Csv(),
            default => new Xlsx(),
        };
    }

    protected function getEnabledLocales(): Collection
    {
        return Collection::make($this->config->get('spreadsheet-translations.locales'));
    }

    protected function getHeaderRowNumber(): int
    {
        return $this->config->get('spreadsheet-translations.header_row_number');
    }

    protected function getKeyColumn(): string
    {
        return $this->config->get('spreadsheet-translations.key_column');
    }
}
