<?php

declare(strict_types=1);

namespace app\components\project;

use app\components\storage\Storage;

/**
 * Class Project
 * @package app\components
 */
class Project {
    private $name;

    private $storage;

    private $uploadToken;

    private $downloadToken;

    /**
     * Project constructor.
     * @param string $name
     * @param Storage $storage
     * @param $uploadToken
     * @param $downloadToken
     */
    public function __construct(
        string $name,
        Storage $storage,
        string $uploadToken,
        string $downloadToken
    )
    {
        $this->name = $name;
        $this->storage = $storage;
        $this->uploadToken = $uploadToken;
        $this->downloadToken = $downloadToken;
    }

    public function availableUploadToken($token)
    {
        return $token === $this->uploadToken;
    }

    public function availableDownloadToken($token)
    {
        return $token === $this->downloadToken;
    }

    public function getDownloadToken()
    {
        return $this->downloadToken;
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
