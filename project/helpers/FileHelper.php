<?php

namespace app\helpers;

use finfo;

class FileHelper extends \yii\helpers\FileHelper
{
    public static function internalBaseConvert($number, $fromBase, $toBase)
    {
        return gmp_strval(gmp_init($number, $fromBase), $toBase);
    }

    /**
     * Split file name on path pieces
     *
     * @param string $name
     * @param int $count
     * @return string[]
     */
    public static function splitNameIntoParts($name, $count = 3)
    {
        static $lengthOfPiece = 2;
        $pieces = [];

        do {
            $pieces[] = substr($name, count($pieces) * $lengthOfPiece, $lengthOfPiece);
        } while (count($pieces) < $count);

        $pieces[] = substr($name, count($pieces) * $lengthOfPiece);

        return $pieces;
    }

    /**
     * @param string $filePath
     * @return string
     */
    public static function getExtension($filePath)
    {
        if ($mime = self::getMimeType($filePath)) {
            return self::getExtensionFromMime($mime);
        }

        $imageInfo = getimagesize($filePath);

        if (isset($imageInfo['mime'])) {
            $extension = explode('/', $imageInfo['mime'])[1];

            return ($extension == 'jpeg' ? 'jpg' : $extension);
        }

        $fileInfo = new finfo(FILEINFO_MIME);

        if ($mime = @$fileInfo->file($filePath)) {
            return self::getExtensionFromMime($mime);
        }

        return false;
    }

    private static function getExtensionFromMime($mime)
    {
        if ($mime) {
            $mime = explode(';', $mime)[0];

            return explode('/', $mime)[1];
        }

        return null;
    }
}