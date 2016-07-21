<?php

namespace app\actions;

use app\components\Image;
use app\helpers\FileHelper;
use app\helpers\UrlHelper;

/**
 * @package actions
 */
class Thumbnail
{
    /**
     * Generate new image by params.
     *
     * @param string $file uri-имя исходного (физического) файла
     * @param string $hash контрольная сумма uri физического файла и параметров
     * @param string $extension расширение (формат) создаваемого файла
     * @param string $params дополнительные параметры конвертации
     * @param null $translit
     * @throws \Exception
     * @throws \HttpException
     */
    public function run($file, $hash, $extension, $params = '', $translit = null)
    {
        $project = $_SERVER['DOMAIN'];

        $image = new Image;

        $fileName = $file . '.' . $extension;

        $generatedHash = FileHelper::internalHash($fileName, $params, \App::$instance->config['downloadToken']);

        if ($generatedHash !== $hash) {
            throw new \Exception(400);
        }

        $filePath = FileHelper::makePath($file, $project, $extension);
        
        $physicalPath = $image->resolvePhysicalPath($filePath);

        if (!$physicalPath) {
            throw new \Exception(404);
        }

        if (in_array($extension, \App::$instance->config['availableImageExtensions'])) {
            $thumbParams = UrlHelper::internalDecodeParams($params);
            $thumbParams['f'] = $extension;

            $image->makeImage($physicalPath, $thumbParams);
        } else {
            throw new \Exception(400);
        }
    }
}
