<?php

namespace app\interfaces;

/**
 * Interface FileWorker
 * @package app\interfaces
 */
interface FileWorker
{
    /**
     * Return file
     *
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public function makeFile($path, $params = []);
}