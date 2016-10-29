<?php

namespace app\components\storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;

/**
 * Class Filesystem
 */
class Storage
{
    /** @var  \League\Flysystem\Filesystem */
    private $_filesystem;

    private $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * @param array $config
     */
    public function configure(array $config)
    {
        $path = rtrim($this->_config['directory'], '/') . DIRECTORY_SEPARATOR . ltrim($config['path'], '/');
        if (!is_dir($path)) {
            mkdir($path);
        }
        $adapter = new Local($path);
        $this->_filesystem = new Filesystem($adapter);
    }

    /**
     * @param string $uploadName
     * @return File
     * @throws FileException
     */
    public function save($uploadName)
    {
        $file = new File($this->_filesystem);
        $file->upload($uploadName);

        return $file;
    }
}
