<?php

declare(strict_types=1);

$container = $app->getContainer();

$app->post('/upload/{project}/{token}', \app\actions\Upload::class)
    ->add(new \app\middleware\Storage($container))
    ->add(new \app\middleware\UploadAuth($container));
