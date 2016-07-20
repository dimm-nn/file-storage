<?php

namespace app\actions;

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
     * @throws \HttpException
     */
    public function run($file, $hash, $extension, $params = '', $translit = null)
    {
        $project = $_SERVER['DOMAIN'];

        $hashPath = $file . '.' . $extension;

        $nameParts = FileHelper::splitNameIntoParts($file);

        $pathPrefix = $project . '/' . implode('/', $nameParts);
        $filePath = $pathPrefix . '.' . $extension;

        if (\App::$instance->image->internalHash($hashPath, $params) !== $hash) {
            throw new \HttpException(400);
        }

        $physicalPath = \App::$instance->image->resolvePhysicalPath($filePath);

        if (!$physicalPath) {
            throw new \HttpException(404);
        }

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        if (in_array($extension, $extensions)) {
            $thumbParams = UrlHelper::internalDecodeParams($params);
            $thumbParams['f'] = $extension;

            \App::$instance->image->makeImage($physicalPath, $thumbParams);
        } else {
            throw new \HttpException(400);
        }
    }
}
