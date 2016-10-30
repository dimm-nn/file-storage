<?php

declare(strict_types=1);

namespace app\actions;

use Interop\Container\ContainerInterface;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class Action
 * @package app\actions
 */
abstract class Action
{
    /** @var \Slim\Container */
    protected $container;

    /** @var  \app\components\storage\Storage */
    protected $storage;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storage = $this->container->get('storage');
    }

    /**
     * @param string $project
     * @param string $method
     * @throws \Slim\Exception\ContainerValueNotFoundException
     */
    protected function init($project, $method)
    {
        $settings = $this->container->get('settings');

        if (!isset($settings['projects'][$project])) {
            throw new ContainerValueNotFoundException();
        }

        $projectSettings = $settings['projects'][$project];

        $this->storage->configure($projectSettings['storage']);
    }
}
