<?php

declare(strict_types=1);

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
     * TODO
     * Make secure hash based on file path, params and download token
     *
     * @param string $filePath
     * @param array $params
     * @param string $downloadToken
     * @return string
     */
    public static function internalHash($filePath, $params, $downloadToken)
    {
        $hash = hash('crc32', $downloadToken . $filePath . $params . $downloadToken);

        $hash = self::baseConvert($hash, 16, 36);

        return str_pad($hash, 5, '0', STR_PAD_LEFT);
    }

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

        if ($extension = self::getExtension($file)) {
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

    public static function getExtension($file)
    {
        static $mimeTypeToExtensionMap;

        if (!$mimeTypeToExtensionMap) {
            $mimeTypeToExtensionMap = array_flip(MimeType::getExtensionToMimeTypeMap());
        }

        $mimeType = Util::guessMimeType($file, file_get_contents($file));

        if (array_key_exists($mimeType, $mimeTypeToExtensionMap)) {
            $extension =  $mimeTypeToExtensionMap[$mimeType];
        } else {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        }

        if (array_key_exists($extension, self::$webExtensionsMap)) {
            $extension = self::$webExtensionsMap[$extension];
        }

        return $extension;
    }
}
