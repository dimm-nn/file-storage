<?php

/**
 * Class App
 * @property array $config
 */
class App
{
    /**
     * @var self
     */
    public static $instance;
    private $_configs;

    private function __construct(array $configs)
    {
        $this->_configs = $configs;
    }

    public static function autoload($className)
    {
        static $classMap;

        if (isset($classMap[$className])) {
            $classFile = $classMap[$className];
        } else {
            $classFile = str_replace('\\', '/', $className) . '.php';

            if (strpos($classFile, 'app') !== false) {
                $classFile = str_replace('app', APP_DIR, $classFile);
            } else {
                $classFile = APP_DIR . '/' . $classFile;
            }

            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        }

        include($classFile);

        if (!class_exists($className, false) && !interface_exists($className, false)) {
            throw new Exception("Unable to find '$className' in file: $classFile.");
        }
    }

    public static function instance(array $configs = [])
    {
        if (empty(self::$instance)) {
            self::$instance = new static($configs);
        }

        return self::$instance;
    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        throw new \Exception('Property `' . $name . '` not exists`');
    }

    public function getConfig()
    {
        return $this->_configs;
    }
}