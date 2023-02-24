<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Generator
{
    protected Worksheet $worksheet;
    protected array $localeColumns = [];
    protected array $translations = [];

    public function __construct(
        protected Repository $config,
        protected Factory $http
    ) {
    }

    public function handle(): void
    {
        $this->loadWorksheet();

        $this->findLocaleColumns();

        // start looping over each row
        foreach ($this->worksheet->getRowIterator() as $row) {
            // ignore if row is header row
            if ($row->getRowIndex() === $this->getHeaderRowNumber()) {
                continue;
            }

            // ignore if row is one of the ignored rows
            if (in_array($row->getRowIndex(), $this->config->get('spreadsheet-translations.ignored_rows'))) {
                continue;
            }

            // get value from key column and parse it into 2 different variables as filename and identifier
            // for example, if key is `auth.login.title`, then filename is `auth`, identifier is `login.title`
            [$filename, $identifier] = $this->parseTranslationKey($row->getColumnIterator());

            // loop over each locale and with its colum coordinate
            foreach ($this->localeColumns as $locale => $localeColumn) {
                if (! isset($this->translations[$locale])) {
                    $this->translations[$locale] = [];
                }

                if (! isset($this->translations[$locale][$filename])) {
                    $this->translations[$locale][$filename] = [];
                }

                /*
                 * fill translations so that end result looks like something like this:
                 *
                 * [
                 *   'en' => [
                 *      'auth' => [
                 *         'login.title' => 'This is title translation for English',
                 *      ]
                 *    ]
                 * ]
                 * */
                $this->translations[$locale][$filename][$identifier] = $row->getColumnIterator()->seek($localeColumn)->current()->getValue();
            }
        }

        $this->generateTranslationFiles();
    }

    protected function loadSpreadsheet(): Spreadsheet
    {
        $filepath = $this->config->get('spreadsheet-translations.filepath');

        // If file path is URL we assume file is needs to be downloaded
        if (filter_var($filepath, FILTER_VALIDATE_URL)) {
            $contents = $this->http->get($filepath)->body();
            fwrite($filepath = tmpfile(), $contents);
            fclose($filepath);
        }

        return $this->getReader()->load($filepath);
    }

    protected function loadWorksheet(): void
    {
        $sheet = $this->config->get('spreadsheet-translations.sheet');

        $spreadsheet = $this->loadSpreadsheet();

        if (is_null($sheet)) {
            $this->worksheet = $spreadsheet->getActiveSheet();

            return;
        }

        $this->worksheet = $spreadsheet->getSheetByName($sheet);
    }

    protected function getHeaderRow(): Row
    {
        return $this->worksheet->getRowIterator()->seek($this->getHeaderRowNumber())->current();
    }

    protected function findLocaleColumns(): void
    {
        // loop over header row columns
        foreach ($this->getHeaderRow()->getCellIterator() as $cell) {
            // ignore if column value is not matching with one of the listed locales
            if (! $this->getLocales()->contains($cell->getValue())) {
                continue;
            }

            // collect listed locales with the colum coordinate on the worksheet
            $this->localeColumns[$cell->getValue()] = $cell->getColumn();
        }
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

    protected function parseTranslationKey(RowCellIterator $columnIterator): array
    {
        $keyColumn = $this->config->get('spreadsheet-translations.key_column');
        $key = $columnIterator->seek($keyColumn)->current()->getValue();

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

    protected function getLocales(): Collection
    {
        return Collection::make($this->config->get('spreadsheet-translations.locales'));
    }

    protected function getHeaderRowNumber(): int
    {
        return $this->config->get('spreadsheet-translations.header_row_number');
    }
}
