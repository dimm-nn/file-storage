<?php

declare(strict_types=1);

use app\actions\Download;
use app\actions\Upload;
use app\middleware\Project;
use app\middleware\UploadAuthMiddleware;

$container = $app->getContainer();

$app->post('/upload/{project}/{token}', Upload::class)
    ->add(new UploadAuthMiddleware($container))
    ->add(new Project($container))
;

$app->group('/{file:\w+}_{hash:\w{1,7}}', function () {
    $this->get('.{extension:\w{3,4}}', Download::class);
    $this->get('/{translit}.{extension:\w{3,4}}', Download::class);
    $this->get('{params:_[\w\_-]+}.{extension:\w{3,4}}', Download::class);
    $this->get('{params:_[\w\_-]+}/{translit}.{extension:\w{3,4}}', Download::class);
})->add(new Project($container));