<?php

declare(strict_types=1);

namespace app\actions;

use app\components\storage\File;
use app\components\storage\FileException;
use app\components\storage\image\ImageEditorInterface;
use app\components\storage\Storage;
use Interop\Container\ContainerInterface;
use League\Flysystem\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Class Download
 * @package app\actions
 */
class Download
{
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
        $this->storage = $container->get('storage');
        $this->imageEditor = $container->get('imageEditor');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        /**
         * @var RouteInterface $route
         */
        $route = $request->getAttribute('route');

        $fileHash = $route->getArgument('file');
        $params = $route->getArgument('params', '');
        $extension = $route->getArgument('extension', '');
        $translit = $route->getArgument('translit', '');

        $fileName = $fileHash . '.' . $extension;

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
