<?php

namespace app\actions;

use app\helpers\FileHelper;
use app\helpers\UrlHelper;
use app\interfaces\FileWorker;
use app\workers\File;
use app\workers\Image;

/**
 * class Download
 * @package app\actions
 */
class Download
{
    /**
     * Get file by params.
     *
     * @param string $file file hash name
     * @param string $hash secure hash (save from DDOS)
     * @param string $extension file extension
     * @param string $params download params
     * @param null $translit seo file name
     */
    public function run($file, $hash, $extension, $params = '', $translit = null)
    {
        $project = $_SERVER['DOMAIN'];

        $fileName = $file . '.' . $extension;

        if (FileHelper::availableHash($hash,$fileName, $params) === false) {
            http_response_code(400);
            exit(0);
        }

        $filePath = FileHelper::makePath($file, $project, $extension);
        
        $physicalPath = FileHelper::resolvePhysicalPath($filePath);

        if (!$physicalPath || !is_file($physicalPath)) {
            http_response_code(404);
            exit(0);
        }

        $params = UrlHelper::internalDecodeParams($params);
        $params['f'] = $extension;
        $params['translit'] = $translit;

        /** @var FileWorker $worker */
        $worker = $this->getWorkerByExtension($extension);

        $worker->makeFile($physicalPath, $params);
    }

    /**
     * Get worker by file extension what process file by params and echo him
     * 
     * @param $extension
     * @return mixed
     */
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
