<?php

namespace app\workers;

use app\helpers\FileHelper;
use app\interfaces\FileWorker;

class File implements FileWorker
{
    public function makeFile($path, $params = [])
    {
        header('Content-Type: ' . FileHelper::getMimeType($path));

        readfile($path);
    }
}