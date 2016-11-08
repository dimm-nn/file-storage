<?php

declare(strict_types=1);

namespace app\middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Container;
use Slim\Exception\ContainerValueNotFoundException;

class Project
{
    /**
     * @var ContainerInterface|Container
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /**
         * @var \Slim\Route $route
         */
        $route = $request->getAttribute('route');

        $project = $route->getArgument('project');

        if (!$project) {
            $queryParams = $request->getQueryParams();

            $project = $queryParams['domain'];
        }

        $this->configure($project);

        return $next($request, $response);
    }

    private function configure(string $name)
    {
        $settings = $this->container->get('settings');

        if (!isset($settings['projects'][$name])) {
            throw new ContainerValueNotFoundException();
        }

        $projectSettings = $settings['projects'][$name];

        /**
         * @var \app\components\storage\Storage $storage
         */
        $storage = $this->container->get('storage');

        $storage->configure($projectSettings['storage']);

        /**
         * @var Container
         */
        $this->container->offsetSet(
            'project',
            new \app\components\project\Project(
                $name,
                $storage,
                $projectSettings['upload']['token'],
                $projectSettings['download']['token']
            )
        );
    }
}
