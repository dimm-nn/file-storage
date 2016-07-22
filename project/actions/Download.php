<?php

namespace app\actions;

use app\components\Image;
use app\helpers\FileHelper;
use app\helpers\UrlHelper;

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

        if (in_array($extension, \App::$instance->config['availableImageExtensions'])) {
            $thumbParams = UrlHelper::internalDecodeParams($params);
            $thumbParams['f'] = $extension;

            (new Image)->makeImage($physicalPath, $thumbParams);
        } else {
            throw new \Exception(400);
        }
    }
}
