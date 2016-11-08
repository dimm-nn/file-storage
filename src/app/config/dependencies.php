<?php

declare(strict_types=1);

use app\actions\Download;
use app\actions\Upload;
use app\components\storage\image\ImagineEditor;
use app\components\storage\Storage;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Slim\Handlers\Strategies\RequestResponseArgs;

return array(
    'foundHandler' => function () {
        return new RequestResponseArgs();
    },
    // monolog
    'logger' => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Monolog\Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));

        return $logger;
    },
    'storage' => function (ContainerInterface $container) {
        /** @var \Slim\Collection $settings */
        $settings = $container->get('settings');

        return new Storage($settings->get('storage'));
    },
    'imageEditor' => function () {
        return new ImagineEditor();
    },
    'project' => function () {
        return new \app\components\project\Project();
    }
);