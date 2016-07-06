<?php

/**
 * @inheritdoc
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication the application instance
     */
    public static $app;
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$container = new yii\di\Container;

/**
 * @inheritdoc
 * @property \components\Image $image
 */
abstract class BaseApplication extends yii\base\Application
{
    public $oldSecurity;
}