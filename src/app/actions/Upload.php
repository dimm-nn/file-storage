<?php

declare(strict_types=1);

namespace app\actions;

use app\components\project\Project;
use app\components\storage\Storage;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Upload
 * @package app\actions
 */
class Upload
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(ContainerInterface $container)
    {
        $this->storage = $container->get('storage');
    }

    public function __invoke(Request $request, Response $response): Response
    {
        /** @var \Slim\Http\UploadedFile[] $files */
        $files = $request->getUploadedFiles();

        $result = [];
        foreach ($files as $name => $file) {
            $result[$file->file] = $this->storage->save($file->file)->getName();
        }
        return $response->withJson($result, 200);
    }
}
