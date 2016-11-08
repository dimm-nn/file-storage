<?php

declare(strict_types=1);

namespace app\middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\ContainerValueNotFoundException;

class UploadAuth
{
    /**
     * @var \app\components\project\Project
     */
    private $project;

    public function __construct(ContainerInterface $container)
    {
        $this->project = $container->get('project');
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
        return $this->project->availableUploadToken($token);
    }
}
