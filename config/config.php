<?php

declare(strict_types=1);

return [

    // array of locale codes that parser should look for in spreadsheet for translations
    'locales' => ['en'],

    // path to spreadsheet file
    // by default, points to `translations.xlsx` file in Laravel project's `lang` directory
    // can also use URL as remote file location
    // when a valid URL is provided parse will try to download the file to a temporary local file and parse it
    'filepath' => lang_path('translations.xlsx'),

    // defines which sheet should be used in spreadsheet file.
    // default is `null`
    // when `null`, parser selects active sheet in the spreadsheet to parse translations from
    // if you want to use a different sheet, provide sheet's name on this parameter
    'sheet' => null,

    // which row should be used as header.
    // header row should contain locale codes that are defined in `locales` parameter
    'header_row_number' => 1,

    // which column should be used for translation keys
    'key_column' => 'A',

    // array of row numbers which should be ignored when translations are parsed
    'ignored_rows' => [],

];
