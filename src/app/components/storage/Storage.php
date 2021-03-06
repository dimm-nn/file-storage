<?php

declare(strict_types=1);

namespace app\components\storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class Filesystem
 */
class Storage
{
    /** @var  \League\Flysystem\Filesystem */
    private $filesystem;

    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     */
    public function configure(array $config)
    {
        $path = rtrim($this->config['directory'], '/') . DIRECTORY_SEPARATOR . ltrim($config['path'], '/');
        if (!is_dir($path)) {
            mkdir($path);
        }
        $adapter = new Local($path);
        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * @param string $uploadFilePath
     * @return File
     * @throws FileException
     */
    public function save($uploadFilePath)
    {
        return (new File($this->filesystem))->upload($uploadFilePath);
    }

    /**
     * @param $fileName
     * @return File
     */
    public function getFileByName($fileName)
    {
        return (new File($this->filesystem))->load($fileName);
    }
}
