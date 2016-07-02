<?php

namespace api\controllers;

use api\components\File;
use api\components\Controller;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * @package api\controllers
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
    public function actionUpload($secret, $project)
    {
        if (!in_array($secret, Yii::$app->params['secret']))
        {
            throw new ForbiddenHttpException();
        }

        $file = Yii::createObject([
            'class' => File::class,
            'projectName' => $project,
        ]);

        $files = $file->save();

        echo json_encode($files);
    }
}
