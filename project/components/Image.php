<?php

namespace app\components;

use app\helpers\FileHelper;
use Imagine\Filter\Transformation;
use Imagine\Gmagick\Imagine;
use Imagine\Image\Box;

/**
 * Class Image
 */
class Image
{
    const DEFAULT_QUALITY = 85;

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
}
