<?php
// DIC configuration

$container = $app->getContainer();

$container['foundHandler'] = function () {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

// monolog
$container['logger'] = function (\Interop\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// storage
$container['storage'] = function (\Interop\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['storage'];
    return new \app\components\storage\Storage($settings);
};
