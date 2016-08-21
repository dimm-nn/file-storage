<?php


namespace app\components\storage;

use League\Flysystem\Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;

/**
 * Class File
 * @package app\components\storage
 */
class File
{
    public $directoryNameLength = 2;

    public $pathDeep = 2;

    private $_name;

    private $_path;

    private $_extension;

    private $_filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    public function load($name)
    {
        $this->_name = $name;
        if (!$this->_filesystem->has($this->getPath())) {
            unset($this->_name);
            throw new FileException("File with name '{$name}' does not exists");
        }
    }

    /**
     * @param string $fileName
     * @throws FileException
     */
    public function upload($fileName)
    {
        if (dirname($fileName) !== sys_get_temp_dir()) {
            throw new FileException('bad file location');
        }

        if (!is_file($fileName)) {
            throw new FileException('file does not exists');
        }

        $newName = FileName::get($fileName);
        $this->_name = $newName;

        try {
            if (!$this->_filesystem->has($this->getPath())) {
                $stream = fopen($fileName, 'r+');
                $this->_filesystem->writeStream($this->getPath(), $stream);
                fclose($stream);
            }
        } catch (Exception $e) {
            unset($this->_name);
            throw new FileException($e->getMessage());
        }

    }

    public function getContent()
    {
        return $this->_filesystem->read($this->getPath());
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->_path) {
            return $this->_path;
        }

        $name = $this->getName();
        $directories = str_split(substr($name, 0, $this->directoryNameLength * $this->pathDeep), $this->pathDeep);

        return $this->_path = implode(DIRECTORY_SEPARATOR, $directories) . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string|null
     * @throws FileException
     */
    public function getExtension()
    {
        if ($this->_extension) {
            return $this->_extension;
        }

        return $this->_extension = pathinfo($this->_name, PATHINFO_EXTENSION);
    }
}
