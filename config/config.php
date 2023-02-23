<?php

/*
 * You can place your custom package configuration in here.
 */
return [

    'locales' => ['en'],

    'type' => 'xlsx', // xlsx or csv

    'filepath' => lang_path('translations.xlsx'),

    'header_row_index' => 0,

    'translation_key_column_index' => 0,

    'ignored_column_indexes' => [],

    'ignored_row_indexes' => [],

];