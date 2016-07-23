<?php

namespace app\helpers;

/**
 * Class UrlHelper
 * @package app\helpers
 */
class UrlHelper
{
    /**
     * Decode string params to array
     *
     * @param string $paramString
     * @return array
     */
    public static function internalDecodeParams($paramString)
    {
        $result = [];
        if (preg_match_all('/_(?:([a-z]{1,4})\-([a-z\d]+))+/i', $paramString, $matches)) {
            foreach ($matches[1] as $idx => $paramName) {
                $result[$paramName] = $matches[2][$idx];
            }
        }

        if (isset($result['b'])) {
            $result['w'] = $result['h'] = $result['b'];
            unset($result['b']);
        }

        return $result;
    }
}