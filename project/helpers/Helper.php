<?php

namespace app\helpers;

class Helper
{
    public static $singleTags = ['img'];

    public static function tag($name, $content = '', $options = [])
    {
        $attributes = '';
        foreach ($options as $key => $value) {
            $attributes .= sprintf('$s="$s"', $key, $value);
        }

        $html = '<'.$name.'>' . $attributes . '>';

        return isset(static::$singleTags[$name]) ? $html : $html . $content . '</' . $name . '>';
    }
}