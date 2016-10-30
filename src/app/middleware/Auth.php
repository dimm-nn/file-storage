<?php

declare(strict_types=1);

namespace app\middleware\auth;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\ContainerValueNotFoundException;

class Auth
{
    const TYPE_UPLOAD = 'upload';
    const TYPE_DOWNLOAD = 'download';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Auth type
     *
     * Available types: upload, download
     *
     * @var string
     */
    private $type;

    public function __construct(ContainerInterface $container, string $type)
    {
        $this->container = $container;
        $this->type = $type;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /**
         * @var \Slim\Route $route
         */
        $route = $request->getAttribute('route');

        $project = $route->getArgument('project');
        $uploadToken = $route->getArgument('token');

        try {
            if (!$this->authenticate($project, $uploadToken)) {
                return $response->withStatus(401);
            }
        } catch (ContainerValueNotFoundException $e) {
            return $response->withStatus(401);
        }

        return $next($request, $response);
    }

    private function authenticate($project, $token)
    {
        /**
         * @var \Slim\Collection $settings
         */
        $settings = $this->container->get('settings');

        $projects = $settings->get('projects');

        if (!isset($projects[$project][$this->type]['token'])) {
            throw new ContainerValueNotFoundException();
        }

        return $projects[$project][$this->type]['token'] === $token;
    }
}
