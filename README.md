## Spreadsheet translations for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orkhanahmadov/spreadsheet-translations.svg?style=flat-square)](https://packagist.org/packages/orkhanahmadov/spreadsheet-translations)
[![Total Downloads](https://img.shields.io/packagist/dt/orkhanahmadov/spreadsheet-translations.svg?style=flat-square)](https://packagist.org/packages/orkhanahmadov/spreadsheet-translations)
![GitHub Actions](https://github.com/orkhanahmadov/spreadsheet-translations/actions/workflows/main.yml/badge.svg)

![Spreadsheet Translation](https://banners.beyondco.de/Spreadsheet%20Translations%20for%20Laravel.png?theme=light&packageManager=composer+require&packageName=orkhanahmadov%2Fspreadsheet-translations&pattern=architect&style=style_1&description=Easily+create+Laravel+translation+files+from+spreadsheet+&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

## Why?

Maintaining multi-language support in Laravel applications can be hard

- Laravel's translation files are in plain PHP/JSON files.
This assumes that the person who's going to translate the application know how to work with PHP/JSON file, which is not always the case
- Each locale translations are localed under different folders. For example, `en` folder for English translations, `de` folder for German.
This separation is good on the code level, but makes it hard to maintain 2+ locale translations.
- It is easy to add one new key and translation for English but forget to do it in German, since there's nothing that forces this or makes it easy to spot.

Alternatively you can store application's translations in a spreadsheet file, something like:

| key                   | en         | de        | es             |
|-----------------------|------------|-----------|----------------|
| dashboard.statistics  | Statistics | Statistik | Estadísticas   |
| login.form.first_name | First name | Vorname   | Nombre de pila |
| login.welcome         | Welcome    | Wilkommen | Bienvenida     |

This solves all above-mentioned problems:

- Translations maintainer does not need to know how to work with PHP or JSON
- All translations are maintained in a single file and view
- Each translation is located under locale column, which makes is very easy to spot missing translations

But now the problem is, Laravel cannot directly work with this spreadsheet file to display translations.

Here comes `spreadsheet-translations` package!
It reads spreadsheet file that contains translations for multiple locales and generates plain JSON files out of it that Laravel can work with.

## Installation

You can install the package via composer:

```bash
composer require orkhanahmadov/spreadsheet-translations
```

Publish config file using:

```bash
php artisan vendor:publish --provider="Orkhanahmadov\SpreadsheetTranslations\SpreadsheetTranslationsServiceProvider"
```

Config file contains following parameters:

- `locales` - array of locale codes that parser should look for in spreadsheet. Default is `['en']`
- `filepath` - path to spreadsheet file. By default, points to `translations.xlsx` file in Laravel project's `lang` directory. This config parameter can also use URL as remote file location. When a valid URL is provided parse will try to download the file to a temporary local file and parse it.
- `sheet` - defines which sheet should be used in spreadsheet file. Default is `null`. When `null`, parser selects active sheet in the spreadsheet to parse translations from. If you want to use a different sheet, provide sheet's name on this parameter.
- `header_row_number` - which row should be used as header. Default is `1`. Header row should contain locale codes that are defined `locales` config parameter
- `key_column` - which column should be used for translation keys. Default is `A` column.
- `ignored_rows` - array of row numbers which should be ignored when translations are parsed. Default is empty array.

## Usage

Let's imagine we have the following Excel spreadsheet file which is located in remote server with public URL `https://example.com/translations.xlsx`.
Spreadsheet contains:

| comments                           | key                   | en         | de        | es             |
|------------------------------------|-----------------------|------------|-----------|----------------|
| Dashboard statistics section title | dashboard.statistics  | Statistics | Statistik | Estadísticas   |
| ignore this row !!!!!              |                       |            |           |                |
| First name field on login form     | login.form.first_name | First name | Vorname   | Nombre de pila |
| Welcome page title                 | login.welcome         | Welcome    | Wilkommen | Bienvenida     |

We want to:

- Point parser to the remote file to download and parse
- Parse only `en` and `de` locale translations
- Use `key` column as key, in this case column `B` in spreadsheet coordinates
- Ignore row number 3

Once we publish the config file we need to make follow adjustments:

```php
[
    'filepath' => 'https://example.com/translations.xlsx', // direct download URL of the file
    'locales' => ['en', 'de'], // parse `en` and `de` translations only, which means `es` will be ignored
    'key_column' => 'B', // sets key column to B
    'ignored_rows' => [3], // ignore row number 3
]
```

Package ships with an artisan command `translations:generate`.

```shell
php artisan translations:generate
```

When executed it generates necessary JSON translation files in Laravel's `lang` directory.

For above spreadsheet file and configuration `translations:generate` will generate following folder and file structure:

- `lang/`
  - `en.json`
    - `{"dashboard.statistics": "Stastitics", "login.form.first_name": "First name", "welcome": "Welcome"}`
  - `de.json`
    - `{"dashboard.statistics": "Statistik", "login.form.first_name": "Vorname", "welcome": "Wilkommen"}`

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Security

If you discover any security related issues, please email hey@orkhan.dev instead of using the issue tracker.

## Credits

-   [Orkhan Ahmadov](https://github.com/orkhanahmadov)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Alternatives

You can check [larswiegers/laravel-translations-checker](https://github.com/LarsWiegers/laravel-translations-checker) if you want to detect missing translations.
