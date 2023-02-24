<?php

declare(strict_types=1);

return [

    'locales' => ['en'],

    'type' => 'xlsx', // xlsx or csv

    'filepath' => lang_path('translations.xlsx'),

    'sheet' => null,

    'header_row_number' => 1,

    'key_column' => 'A',

    'ignored_rows' => [],

];
