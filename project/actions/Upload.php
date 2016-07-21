<?php

namespace app\actions;

use app\components\File;

/**
 * @package controllers
 */
class Upload
{
    /**
     * Upload files from $_FILES and $_POST['url] arrays
     * Return json answer with files names
     *
     * @param string $project
     * @param string $uploadToken secret token (for auth)
     * @throws \Exception
     */
    public function run($project, $uploadToken)
    {
        if (!in_array($uploadToken, \App::instance()->config['uploadToken'])) {
            throw new \Exception(403);
        }

        $file = new File($project);
        $result = [];

        // Save files
        foreach ($_FILES as $name => $uploadedFile) {
            $result[$name] = $file->saveRaw($uploadedFile);
        }

        // Save files by url
        if ($urls = $_POST['urls'] ?? []) {
            $urlBlocks = array_chunk($urls, 7);

            foreach ($urlBlocks as $urlBlock) {
                $result = array_merge($result, $file->bulkLoad($urlBlock));
            }
        }

        echo json_encode($result);
    }
}
