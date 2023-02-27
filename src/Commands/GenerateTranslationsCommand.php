<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Commands;

use Illuminate\Console\Command;
use Orkhanahmadov\SpreadsheetTranslations\SpreadsheetParser;
use Orkhanahmadov\SpreadsheetTranslations\TranslationFileGenerator;

class GenerateTranslationsCommand extends Command
{
    protected $signature = 'translations:generate';

    protected $description = 'Generates app translations from spreadsheet file';

    public function handle(
        SpreadsheetParser $parser,
        TranslationFileGenerator $fileGenerator
    ): int {
        $translations = $parser->parse()->getTranslations();

        foreach ($translations as $locale => $groups) {
            $this->alert("Generating translation files for {$locale}...");

            $fileGenerator->generate($locale, $groups);

            $this->info("Generated translation files for {$locale}!");
        }

        return Command::SUCCESS;
    }
}
