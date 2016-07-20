<?php

namespace app\components;

use app\helpers\FileHelper;
use Imagine\Filter\Transformation;
use Imagine\Gmagick\Imagine;
use Imagine\Image\Box;

/**
 * Class Image
 * @package common\helpers
 */
class Image
{
    const DEFAULT_QUALITY = 85;

    public $downloadToken;

    /**
     * Make operation to image
     *
     * Available operations:
     * - w - generate thumbnail with width equal `w` (default - original)
     * - h - generate thumbnail with height equal `h` (default - original)
     * - q - quality of thumbnail (default - 85%)
     *
     * @param string $imagePath
     * @param array $params
     */
    public function makeImage($imagePath, $params)
    {
        $imagine = new Imagine;
        $transformation = new Transformation();
        $image = $imagine->open($imagePath);
        $options = [];

        $format = FileHelper::getExtension($imagePath);

        // Thumbnail
        if (!empty($params['w']) || !empty($params['h'])) {
            $box = new Box(
                (int) ($params['w'] ?? $params['h']),
                (int) ($params['h'] ?? $params['w'])
            );

            $transformation->thumbnail($box);
        }

        $quality = $params['q'] ?? self::DEFAULT_QUALITY;

        switch ($format) {
            case 'png':
                $options['png_compression_filter'] = ceil($quality / 10);
                break;
            case 'jpg':
            case 'jpeg':
            case 'pjpeg':
                $options['jpeg_quality'] = $quality;
                break;
        }

        $transformation->apply($image)->show($format, $options);
    }

    /**
     * @param $fileName
     * @return mixed|string
     */
    public function getDomain($fileName)
    {
        return 'http://'
            . substr($fileName, 0, 1)
            . '.' . $_SERVER['DOMAIN'];
    }

    /**
     * @param $src
     * @param array $params
     * @param string $translit
     * @param null $default
     * @return null|string
     */
    public function absoluteUrl($src, array $params = [], $translit = '', $default = null)
    {
        if (!$src) {
            return $default;
        }

        $pathInfo = pathinfo($src);
        $fileName = $pathInfo['filename'];

        if (!empty($params['f'])) {
            $pathInfo['extension'] = $params['f'];
            unset($params['f']);
        }

        ksort($params);

        $encodedParams = $this->encodeParams($params);

        $result = $this->getDomain($fileName)
            . $fileName
            . '_'
            . $this->internalHash($src, $encodedParams)
            . $encodedParams;

        if ($translit) {
            $result .= '/'.$translit;
        }

        if (!empty($pathInfo['extension'])) {
            $result .='.'.$pathInfo['extension'];
        }

        return $result;
    }

    /**
     * @param array $params
     * @return string
     */
    private function encodeParams(array $params)
    {
        $result = '';
        foreach ($params as $key => $value) {
            $result .= '_'.$key.'-'.$value;
        }
        return $result;
    }

    /**
     * По uri-имени возвращает путь к файлу-оригиналу или false если он не найден.
     * @param string $webPath
     * @return string|boolean
     */
    public function resolvePhysicalPath($webPath)
    {
        $storagePath = STORAGE_DIR . '/';

        if (is_file($storagePath.$webPath))
            return $storagePath.$webPath;

        $pathInfo = pathinfo($webPath);
        $symlinkPath = $storagePath.$pathInfo['dirname'].'/'.$pathInfo['filename'];

        if (is_link($symlinkPath))
            return readlink($symlinkPath);

        return false;
    }

    /**
     * @param $filePath
     * @param $params
     * @return string
     */
    public function internalHash($filePath, $params)
    {
        $hash = hash(
            'crc32',
            $this->downloadToken . $filePath . $params . $this->downloadToken
        );

        return str_pad(FileHelper::internalBaseConvert($hash, 16, 36), 5, '0', STR_PAD_LEFT);
    }
}
