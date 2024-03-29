<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetParser
{
    protected Worksheet $worksheet;
    protected array $localeColumns = [];
    protected array $translations = [];

    public function __construct(
        protected Repository $config,
        protected SpreadsheetFileHandler $fileHandler
    ) {}

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function parse(): self
    {
        $this->worksheet = $this->loadWorksheet();

        $this->findLocaleColumns();

        // loop over each row
        foreach ($this->worksheet->getRowIterator() as $row) {
            if ($this->rowShouldBeIgnored($row)) {
                continue;
            }

            $this->parseRow($row);
        }

        return $this;
    }

    protected function rowShouldBeIgnored(Row $row): bool
    {
        // ignore if row is the header row
        if ($row->getRowIndex() === $this->getHeaderRowNumber()) {
            return true;
        }

        // ignore if row is one of the ignored rows
        return in_array(
            $row->getRowIndex(),
            $this->config->get('spreadsheet-translations.ignored_rows'),
            true
        );
    }

    protected function parseRow(Row $row): void
    {
        // get value from key column
        $translationKey = $this->parseTranslationKey($row->getColumnIterator());

        // ignore row if translation key is empty
        if (empty($translationKey)) {
            return;
        }

        // loop over each locale and with its colum coordinate
        // fill translations so that end result looks like something like this:
        /*
         * [
         *   'en' => [
         *      'auth.login.title' => 'This is title translation for English',
         *    ]
         * ]
         */
        foreach ($this->localeColumns as $locale => $localeColumn) {
            $value = $row->getColumnIterator()->seek($localeColumn)->current()?->getValue();

            if (is_null($value)) {
                continue;
            }

            $this->translations[$locale][$translationKey] = $value;
        }
    }

    protected function loadWorksheet(): Worksheet
    {
        $xlsx = new Xlsx();
        $spreadsheet = $xlsx->load($this->fileHandler->getFilePath());

        $sheetName = $this->config->get('spreadsheet-translations.sheet');

        if (is_null($sheetName)) {
            return $spreadsheet->getActiveSheet();
        }

        throw_unless(
            $spreadsheet->sheetNameExists($sheetName),
            new Exception("{$sheetName} sheet does not exist.")
        );

        return $spreadsheet->getSheetByName($sheetName); // @phpstan-ignore-line
    }

    protected function parseTranslationKey(RowCellIterator $columnIterator): string
    {
        $translationKeyValue = $columnIterator
            ->seek($this->config->get('spreadsheet-translations.key_column'))
            ->current()
            ?->getValue();

        return trim($translationKeyValue ?? '');
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

    protected function getHeaderRow(): Row
    {
        return $this->worksheet
            ->getRowIterator()
            ->seek($this->getHeaderRowNumber())
            ->current();
    }

    protected function getLocales(): Collection
    {
        return new Collection($this->config->get('spreadsheet-translations.locales'));
    }

    protected function getHeaderRowNumber(): int
    {
        return $this->config->get('spreadsheet-translations.header_row_number');
    }
}
