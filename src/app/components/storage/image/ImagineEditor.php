<?php

declare(strict_types=1);

namespace app\components\storage\image;

use app\components\storage\FileName;
use Imagine\Filter\Transformation;
use Imagine\Gmagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Class Image
 * @package app\workers
 */
class ImagineEditor implements ImageEditorInterface
{
    const DEFAULT_QUALITY = 85;

    /**
     * @param string $fileContent
     * @param array $params
     * @return string
     * @throws \Imagine\Exception\RuntimeException
     */
    public function applyParams(string $fileContent, array $params = [])
    {
        $path = tempnam(sys_get_temp_dir(), 'image');

        file_put_contents($path, $fileContent);

        $imagine = new Imagine;
        $transformation = new Transformation();
        $image = $imagine->open($path);
        $options = [];
        $thumbnailMode = ImageInterface::THUMBNAIL_INSET;

        if (array_key_exists('zc', $params)) {
            $thumbnailMode = ImageInterface::THUMBNAIL_OUTBOUND;
        }

        $format = FileName::getExtension($path);

        // Thumbnail
        if (!array_key_exists('w', $params) || !array_key_exists('h', $params)) {
            $box = new Box(
                (int) ($params['w'] ?? $params['h']),
                (int) ($params['h'] ?? $params['w'])
            );

            $transformation->thumbnail($box, $thumbnailMode);
        }

        $quality = $params['q'] ?? self::DEFAULT_QUALITY;

        $options = array_merge($options, $this->getQualityOptions($format, $quality));

        /**
         * @var ImageInterface $imagine
         */
        $imagine = $transformation->apply($image);

        return $imagine->show($format, $options);
    }

    /**
     * @param string $format
     * @param int $quality
     * @return array
     */
    private function getQualityOptions($format, $quality)
    {
        $options = [];

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

        return $options;
    }
}