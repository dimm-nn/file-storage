<?php


namespace app\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Download
 * @package app\actions
 */
class Download extends Action
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        echo 'upload';
        return $response;
    }
}
