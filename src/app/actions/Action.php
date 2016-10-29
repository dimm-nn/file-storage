<?php


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
    protected $ci;

    /** @var  \app\components\storage\Storage */
    protected $storage;

    protected $project;

    protected $token;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->storage = $this->ci->get('storage');
    }

    abstract protected function authenticate($token);

    /**
     * @param string $project
     * @param string $method
     */
    protected function init($project, $method)
    {
        if (!isset($this->ci->get('settings')['projects'][$project])) {
            throw new ContainerValueNotFoundException();
        }
        $this->project = $project;

        $settings = $this->ci->get('settings')['projects'][$this->project];
        $this->token = $settings[$method]['token'];

        $this->storage->configure($settings['storage']);
    }
}
