<?php

declare(strict_types=1);

return [
    'foundHandler' => function () {
        return new \Slim\Handlers\Strategies\RequestResponseArgs();
    },
    // monolog
    'logger' => function (\Interop\Container\ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Monolog\Logger($settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

        return $logger;
    },
    'storage' => function (\Interop\Container\ContainerInterface $container) {
        /** @var \Slim\Collection $settings */
        $settings = $container->get('settings');

        return new \app\components\storage\Storage($settings->get('storage'));
    },
    'imageEditor' => function () {
        return new \app\components\storage\image\ImagineEditor();
    }
];