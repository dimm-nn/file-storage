<?php


namespace app\components\storage;

use League\Flysystem\Util;
use League\Flysystem\Util\MimeType;

/**
 * Class Uploader
 * @internal
 * @package app\components\storage
 */
class FileName
{

    /**
     * File extensions map for browsers
     * @var array
     */
    protected static $webExtensionsMap = [
        'jpe' => 'jpeg'
    ];

    /**
     * Generate filename for upload
     * @param string $file
     * @param int $length
     * @return string
     */
    public static function get($file, $length = 13)
    {
        $sha = sha1_file($file);
        $hash = self::baseConvert($sha, 16, 36);

        $name = substr($hash, 0, $length);

        if (strlen($name) < $length) {
            $name = str_pad($name, $length, '0', STR_PAD_LEFT);
        }

        static $mimeTypeToExtensionMap;

        if (!$mimeTypeToExtensionMap) {
            $mimeTypeToExtensionMap = array_flip(MimeType::getExtensionToMimeTypeMap());
        }

        $mimeType = Util::guessMimeType($file, file_get_contents($file));

        if (isset($mimeTypeToExtensionMap[$mimeType])) {
            $extension =  $mimeTypeToExtensionMap[$mimeType];
        } else {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        if (isset(self::$webExtensionsMap[$extension])) {
            $extension = self::$webExtensionsMap[$extension];
        }

        if (!empty($extension)) {
            $name = $name . '.' . $extension;
        }

        return $name;
    }

    /**
     * @param string $number
     * @param int $fromBase
     * @param int $toBase
     * @return string
     */
    private static function baseConvert($number, $fromBase, $toBase)
    {
        return gmp_strval(gmp_init($number, $fromBase), $toBase);
    }



}
