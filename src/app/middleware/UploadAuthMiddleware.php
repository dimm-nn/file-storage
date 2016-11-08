<?php

declare(strict_types=1);

namespace app\middleware;

use app\components\project\Project;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\ContainerValueNotFoundException;

class UploadAuthMiddleware
{
    /**
     * @var ContainerInterface
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

        $uploadToken = $route->getArgument('token');

        try {
            if (!$this->authenticate($uploadToken)) {
                return $response->withStatus(401);
            }
        } catch (ContainerValueNotFoundException $e) {
            return $response->withStatus(401);
        }

        return $next($request, $response);
    }

    private function authenticate($token)
    {
        /**
         * @var Project $project
         */
        $project = $this->container->get('project');

        return $project->availableUploadToken($token);
    }
}
