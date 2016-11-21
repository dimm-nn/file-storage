<?php

declare(strict_types=1);

namespace app\middleware;

use app\exceptions\ProjectNotSetException;
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

    /**
     * @var \app\components\project\Project
     */
    private $project;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->project = $container->get('project');
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $project = '';

            $host = $request->getServerParam('HTTP_HOST');
            if (preg_match('/^(?<subdomain>\w+)\.(?<domain>.+)\.(?<tld>\w+)$/i', $host, $m))
            {
                $project = $m['domain'];
            }

            if (!$project) {
                throw new ProjectNotSetException();
            }

            $this->configure($project);

        } catch (ProjectNotSetException $e) {
            return $response->withStatus(400, $e->getMessage());
        } catch (ContainerValueNotFoundException $e) {
            return $response->withStatus(400, $e->getMessage());
        }

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

        $this->project->configure(
            $name,
            $projectSettings['upload']['token'],
            $projectSettings['download']['token']
        );
    }
}
