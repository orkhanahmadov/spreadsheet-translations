<?php

declare(strict_types=1);

if (! function_exists('lang_path')) {
    function lang_path(string $path = ''): string
    {
        if (! empty($path)) {
            $path = 'lang' . DIRECTORY_SEPARATOR . $path;
        }

        return resource_path($path);
    }
}
