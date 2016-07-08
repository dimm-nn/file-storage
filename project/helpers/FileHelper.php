<?php

namespace app\helpers;

use finfo;

class FileHelper extends \yii\helpers\FileHelper
{
    public static function internalBaseConvert($number, $fromBase, $toBase)
    {
        $str = trim($number);
        if (intval($fromBase) != 10) {
            $len = strlen($str);
            $q = 0;

            for ($i = 0; $i < $len; $i++) {
                $r = base_convert($str[$i], $fromBase, 10);
                $q = \bcadd(bcmul($q, $fromBase), $r);
            }
        } else {
            $q = $str;
        }

        if (intval($toBase) != 10) {
            $s = '';
            while (bccomp($q, '0', 0) > 0) {
                $r = intval(bcmod($q, $toBase));
                $s = base_convert($r, 10, $toBase) . $s;
                $q = bcdiv($q, $toBase, 0);
            }
        } else {
            $s = $q;
        }

        return $s;
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
        $mime = self::getMimeType($filePath);

        if ($mime) {
            return self::getExtensionFromMime($mime);
        }

        $imageInfo = getimagesize($filePath);

        if (isset($imageInfo['mime'])) {
            $extension = explode('/', $imageInfo['mime'])[1];

            return ($extension == 'jpeg' ? 'jpg' : $extension);
        }

        $fileInfo = new finfo(FILEINFO_MIME);
        $mime = $fileInfo->file($filePath);

        if ($mime) {
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