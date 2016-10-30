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

    protected $project;

    protected $token;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storage = $this->container->get('storage');
    }

    abstract protected function authenticate($token);

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

        $this->project = $project;

        $projectSettings = $settings['projects'][$this->project];
        $this->token = $projectSettings[$method]['token'];

        $this->storage->configure($projectSettings['storage']);
    }
}
