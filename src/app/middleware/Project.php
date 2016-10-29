<?php

namespace app\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Project
 */
class Project extends Middleware
{

    private $_name;

    public function getName()
    {
        return $this->_name;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->_name = $request->getAttribute('name');
        $this->configure();


        return parent::__invoke($request, $response, $next);
    }

    private function configure()
    {
        
        $settings = $this->ci->get('settings')[$this->_name];
        $components = $settings['components'];
        unset($settings['middleware']);
        foreach ($settings as $name => $value) {
            $this->$name = $value;
        }

        foreach ($components as $name => $config) {

        }

    }
}
