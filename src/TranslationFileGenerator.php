<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

/**
 * @interal
 */
class TranslationFileGenerator
{
    public function generate(string $locale, array $translations): void
    {
        file_put_contents(
            lang_path("{$locale}.json"),
            json_encode($translations)
        );
    }
}
