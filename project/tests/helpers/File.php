<?php

namespace Tests\helpers;

/**
 * Class File
 */
class File
{

    public static function moveFilesToTemp($files)
    {
        $result = [];
        foreach ($files as $name) {
            $tempName = self::moveFileToTemp($name);
            $result[] = $tempName;
        }
        return $result;
    }

    public static function moveFileToTemp($name)
    {
        $temp = sys_get_temp_dir();
        $tempName = $temp . DIRECTORY_SEPARATOR . basename($name);
        copy(__DIR__ . '/../files/' . $name, $tempName);
        return $tempName;
    }
}
