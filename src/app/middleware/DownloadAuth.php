<?php

declare(strict_types=1);

namespace app\middleware;

use app\components\storage\FileName;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\ContainerValueNotFoundException;

class DownloadAuth
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

        $hash = $route->getArgument('hash');
        $file = $route->getArgument('file');
        $params = $route->getArgument('params', '');
        $extension = $route->getArgument('extension', '');

        $fileName = $file . '.' . $extension;

        try {
            if (!$this->authenticate($hash, $fileName, $params)) {
                return $response->withStatus(401);
            }
        } catch (ContainerValueNotFoundException $e) {
            return $response->withStatus(401);
        }

        return $next($request, $response);
    }

    private function authenticate(string $hash, string $fileName, $params = '')
    {
        $downloadToken = $this->project->getDownloadToken();

        $newHash = FileName::internalHash($fileName, $params, $downloadToken);

        return $newHash === $hash;
    }
}
