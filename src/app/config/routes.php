<?php

declare(strict_types=1);

$app->post('/upload/{project}/{token}', \app\actions\Upload::class)
    ->add(new \app\middleware\UploadAuth($app->getContainer()));
