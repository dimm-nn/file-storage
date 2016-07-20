<?php

/**
 * Class App
 * @property array $config
 * @property app\components\Image $image
 */
class App
{
    /**
     * @var self
     */
    public static $instance;
    private $_configs;
    private $_components;

    private function __construct(array $configs)
    {
        $this->_configs = $configs;

        $this->initComponents();
    }

    private function initComponents()
    {
        $components = $this->_configs['components'] ?? [];

        foreach ($components as $componentName => $component) {
            $class = $component['class'] ?? false;
            unset($component['class']);

            if ($class) {
                $componentObject = new $class;

                foreach ($component as $key => $value) {
                    $componentObject->$key = $value;
                }

                $this->_components[$componentName] = $componentObject;
            }
        }
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

        return $this->__get($name);
    }

    public function getConfig()
    {
        return $this->_configs;
    }
}