<?php

namespace app\actions;

use app\components\FileSaver;

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
     * @param string $uploadToken secret key (for auth)
     * @throws \Exception
     */
    public function run($project, $uploadToken)
    {
        if (!in_array($uploadToken, \App::instance()->config['uploadToken'])) {
            throw new \Exception(403);
        }

        $fileComponent = new FileSaver($project);

        echo json_encode($fileComponent->upload());
    }
}
