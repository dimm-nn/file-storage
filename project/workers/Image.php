<?php

namespace app\workers;

use app\helpers\FileHelper;
use app\interfaces\FileWorker;
use Imagine\Filter\Transformation;
use Imagine\Gmagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Class Image
 * @package app\workers
 */
class Image implements FileWorker
{
    const DEFAULT_QUALITY = 85;

    /**
     * Make operation to image
     *
     * Available operations:
     * - w - generate thumbnail with width equal `w` (default - original)
     * - h - generate thumbnail with height equal `h` (default - original)
     * - q - quality of thumbnail (default - 85%)
     * - zc - origin image proportions (default - equal)
     *
     * @param $path
     * @param $params
     * @return mixed|void
     */
    public function makeFile($path, $params = [])
    {
        $imagine = new Imagine;
        $transformation = new Transformation();
        $image = $imagine->open($path);
        $options = [];
        $thumbnailMode = ImageInterface::THUMBNAIL_INSET;

        if (isset($params['zc'])) {
            $thumbnailMode = ImageInterface::THUMBNAIL_OUTBOUND;
        }

        $format = FileHelper::getExtension($path);

        // Thumbnail
        if (!empty($params['w']) || !empty($params['h'])) {
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
        $imagine->show($format, $options);
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