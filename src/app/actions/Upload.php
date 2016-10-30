<?php

declare(strict_types=1);

namespace app\actions;

use Slim\Exception\ContainerValueNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Upload
 * @package app\actions
 */
class Upload extends Action
{
    public function authenticate($token)
    {
        return $this->token === $token;
    }

    public function __invoke(Request $request, Response $response, string $project, string $token): Response
    {
        try {
            $this->init($project, 'upload');
        } catch (ContainerValueNotFoundException $e) {
            return $response->withStatus(400);
        }

        if (!$this->authenticate($token)) {
            return $response->withStatus(401);
        }

        /** @var \Slim\Http\UploadedFile[] $files */
        $files = $request->getUploadedFiles();

        $result = [];
        foreach ($files as $name => $file) {
            $result[$file->file] = $this->storage->save($file->file)->getName();
        }
        return $response->withJson($result, 200);
    }
}
