<?php

declare(strict_types=1);

namespace Orkhanahmadov\SpreadsheetTranslations;

use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Factory;

class SpreadsheetFileHandler
{
    /** @var false|resource  */
    protected $tmpFile;

    public function __construct(
        protected Repository $config,
        protected Factory $http
    ) {
    }

    public function getFilePath(): string
    {
        // check if file is remote, if so download and store it locally
        if ($this->isRemoteFile()) {
            return $this->temporarilyStoreRemoteFile();
        }

        return $this->filePathConfig();
    }

    protected function temporarilyStoreRemoteFile(): string
    {
        $this->tmpFile = tmpfile();

        throw_if(
            $this->tmpFile === false,
            new Exception('Could not create temporary file!')
        );

        // download remote file
        fwrite($this->tmpFile, $this->getRemoteFileContents());

        return stream_get_meta_data($this->tmpFile)['uri'];
    }

    protected function getRemoteFileContents(): string
    {
        return $this->http
            ->throw()
            ->get($this->filePathConfig())
            ->body();
    }

    protected function isRemoteFile(): bool
    {
        return filter_var($this->filePathConfig(), FILTER_VALIDATE_URL) !== false;
    }

    protected function filePathConfig(): string
    {
        return $this->config->get('spreadsheet-translations.filepath');
    }

    public function __destruct()
    {
        if (! isset($this->tmpFile)) {
            return;
        }

        fclose($this->tmpFile);
    }
}