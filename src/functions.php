<?php

declare(strict_types=1);

if (! function_exists('lang_path')) {
    function lang_path(string $path = ''): string
    {
        return resource_path("lang/{$path}");
    }
}
