<?php

declare(strict_types=1);

namespace app\components\storage;

use League\Flysystem\Exception;
use League\Flysystem\Filesystem;

/**
 * Class File
 * @package app\components\storage
 */
class File
{
    public $directoryNameLength = 2;

    public $pathDeep = 2;

    private $name;

    private $path;

    private $extension;

    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function load($name)
    {
        $this->name = $name;
        if (!$this->filesystem->has($this->getPath())) {
            unset($this->name);
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
        $this->name = $newName;

        try {
            if (!$this->filesystem->has($this->getPath())) {
                $stream = fopen($fileName, 'r+');
                $this->filesystem->writeStream($this->getPath(), $stream);
                fclose($stream);
            }
        } catch (Exception $e) {
            unset($this->name);
            throw new FileException($e->getMessage());
        }

    }

    public function getContent()
    {
        return $this->filesystem->read($this->getPath());
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        $name = $this->getName();
        $directories = str_split(substr($name, 0, $this->directoryNameLength * $this->pathDeep), $this->pathDeep);

        return $this->path = implode(DIRECTORY_SEPARATOR, $directories) . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     * @throws FileException
     */
    public function getExtension()
    {
        if ($this->extension) {
            return $this->extension;
        }

        return $this->extension = pathinfo($this->name, PATHINFO_EXTENSION);
    }
}
