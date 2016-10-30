<?php

declare(strict_types=1);

use app\actions\Upload;
use app\middleware\auth\Auth;
use app\middleware\Storage;

$container = $app->getContainer();

$app->post('/upload/{project}/{token}', Upload::class)
    ->add(new Storage($container))
    ->add(new Auth($container, Auth::TYPE_UPLOAD));
