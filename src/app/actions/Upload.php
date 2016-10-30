<?php

declare(strict_types=1);

namespace app\actions;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Upload
 * @package app\actions
 */
class Upload extends Action
{
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
