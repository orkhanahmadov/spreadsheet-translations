<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations\Commands;

use Illuminate\Console\Command;
use Orkhanahmadov\SpreadsheetTranslations\Generator;

class GenerateTranslationsCommand extends Command
{
    protected $signature = 'translations:generate';

    protected $description = 'Generates app translations from Excel file';

    public function handle(Generator $generator): int
    {
        $generator->handle();

        return Command::SUCCESS;
    }
}
