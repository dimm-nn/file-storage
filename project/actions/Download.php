<?php

namespace app\actions;

use app\helpers\FileHelper;
use app\helpers\UrlHelper;
use app\interfaces\FileWorker;
use app\workers\File;
use app\workers\Image;

/**
 * @package actions
 */
class Download
{
    /**
     * Get file by params.
     *
     * @param string $file file hash name
     * @param string $hash secure hash based on file name and params
     * @param string $extension file extension
     * @param string $params download params
     * @param null $translit
     * @throws \Exception
     * @throws \HttpException
     */
    public function run($file, $hash, $extension, $params = '', $translit = null)
    {
        $project = $_SERVER['DOMAIN'];

        $fileName = $file . '.' . $extension;

        if (FileHelper::availableHash($hash,$fileName, $params) === false) {
            throw new \Exception(400);
        }

        $filePath = FileHelper::makePath($file, $project, $extension);
        
        $physicalPath = FileHelper::resolvePhysicalPath($filePath);

        if (!$physicalPath) {
            throw new \Exception(404);
        }

        $thumbParams = UrlHelper::internalDecodeParams($params);
        $thumbParams['f'] = $extension;

        /** @var FileWorker $worker */
        $worker = $this->getWorkerByExtension($extension);

        $worker->makeFile($physicalPath, $thumbParams);
    }

    public function getWorkerByExtension($extension)
    {
        $class = File::class;

        switch ($extension)
        {
            case 'jpg':
            case 'png':
            case 'jpeg':
            case 'tiff':
            case 'bmp':
                $class = Image::class;
        }

        return new $class;
    }
}
