<?php

declare(strict_types=1);

namespace app\actions;

use Interop\Container\ContainerInterface;

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
}
