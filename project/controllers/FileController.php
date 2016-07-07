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
     * @param string $secret secret key (for auth)
     * @param string $project
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpload($project, $secret)
    {
        if (!in_array($secret, Yii::$app->params['secret'])) {
            throw new ForbiddenHttpException();
        }

        $fileComponent = new FileSaver($project);

        echo json_encode($fileComponent->save());
    }
}
