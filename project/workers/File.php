<?php

namespace app\workers;

use app\helpers\FileHelper;
use app\interfaces\FileWorker;

/**
 * Class File
 * @package app\workers
 */
class File implements FileWorker
{
    /**
     * @inheritdoc
     */
    public function makeFile($path, $params = [])
    {
        $mimeType = FileHelper::getMimeType($path);
        $fileName = $params['translit'] ? $params['translit'] : basename($path);

        header('Content-Type: ' . $mimeType);
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($path));
        header("Content-Disposition: attachment; filename=".$fileName);

        readfile($path);
    }
}