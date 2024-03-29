<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Str;

/**
 * @interal
 */
class SpreadsheetFileHandler
{
    public function __construct(
        protected Application $application,
        protected Repository $config,
        protected Factory $http
    ) {}

    public function getFilePath(): string
    {
        // check if file is remote, if so download and store it locally
        if ($this->isRemoteFile()) {
            return $this->locallyStoredRemoteFile();
        }

        return $this->filePathConfig();
    }

    protected function locallyStoredRemoteFile(): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'spreadsheet');

        throw_if(
            $tmpFile === false,
            new Exception('Could not create temporary file!')
        );

        // download remote file
        file_put_contents($tmpFile, $this->getRemoteFileContents());

        return $tmpFile;
    }

    protected function getRemoteFileContents(): string
    {
        if ($this->laravelVersion() > 8) {
            $this->http->throw();
        }

        return $this->http->get($this->filePathConfig())->body();
    }

    protected function isRemoteFile(): bool
    {
        return filter_var($this->filePathConfig(), FILTER_VALIDATE_URL) !== false;
    }

    protected function filePathConfig(): string
    {
        return $this->config->get('spreadsheet-translations.filepath');
    }

    protected function laravelVersion(): int
    {
        return (int) Str::of($this->application->version())->explode('.')->first();
    }
}
