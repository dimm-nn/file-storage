<?php

declare(strict_types=1);

namespace app\components\storage\image;

interface ImageEditorInterface
{
    /**
     * @param string $fileContent
     * @param array $params
     * @return mixed
     */
    public function applyParams(string $fileContent, array $params = []);
}