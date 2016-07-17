<?php

namespace app\controllers;

use app\components\Controller;
use app\components\FileSaver;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * @package controllers
 */
class FileController extends Controller
{
    /**
     * Upload files from $_FILES and $_POST['url] arrays
     * Return json answer with files names
     *
     * @param string $uploadToken secret key (for auth)
     * @param string $project
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpload($project, $uploadToken)
    {
        if (!in_array($uploadToken, Yii::$app->params['uploadToken'])) {
            throw new ForbiddenHttpException();
        }

        $fileComponent = new FileSaver($project);

        echo json_encode($fileComponent->upload());
    }
}
