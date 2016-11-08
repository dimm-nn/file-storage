<?php

declare(strict_types=1);

// Application middleware

$app->add(new \app\middleware\Project($app->getContainer()));