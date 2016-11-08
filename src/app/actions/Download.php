<?php

declare(strict_types=1);

namespace app\actions;

use app\components\project\Project;
use app\components\storage\File;
use app\components\storage\FileException;
use app\components\storage\FileName;
use app\components\storage\image\ImageEditorInterface;
use app\components\storage\Storage;
use Interop\Container\ContainerInterface;
use League\Flysystem\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Download
 * @package app\actions
 */
class Download
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var ImageEditorInterface
     */
    private $imageEditor;

    public function __construct(ContainerInterface $container)
    {
        $this->project = $container->get('project');
        $this->storage = $container->get('storage');
        $this->imageEditor = $container->get('imageEditor');
    }

    private function availableHash(string $hash, string $fileName, string $params)
    {
        $downloadToken = $this->project->getDownloadToken();

        $newHash = FileName::internalHash($fileName, $params, $downloadToken);

        return $newHash === $hash;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $fileHash
     * @param string $hash
     * @param string $params
     * @param string $extension
     * @param string $translit
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $fileHash,
        string $hash,
        string $params,
        string $extension,
        string $translit = ''
    )
    {
        $fileName = $fileHash . '.' . $extension;

        if ($this->availableHash($hash, $fileName, $params) === false) {
            return $response->withStatus(401);
        }

        try {
            /**
             * @var File $file
             */
            $file = $this->storage->getFileByName($fileName);
        } catch (FileException $exception) {
            return $response->withStatus(404);
        }

        $params = self::internalDecodeParams($params);
        $params['f'] = $extension;
        $params['translit'] = $translit;

        $mimeType = Util::guessMimeType($file->getName(), $file->getContent());

        $body = $response->getBody();

        $body->write($this->imageEditor->applyParams($file->getContent(), $params));

        return $response->withHeader('Content-Type', $mimeType)->withBody($body);
    }

    /**
     * Decode string params to array
     *
     * @param string $paramString
     * @return array
     */
    public static function internalDecodeParams(string $paramString)
    {
        $result = [];
        if (preg_match_all('/_(?:([a-z]{1,4})\-([a-z\d]+))+/i', $paramString, $matches)) {
            foreach ($matches[1] as $idx => $paramName) {
                $result[$paramName] = $matches[2][$idx];
            }
        }

        if (isset($result['b'])) {
            $result['w'] = $result['h'] = $result['b'];
            unset($result['b']);
        }

        return $result;
    }
}
