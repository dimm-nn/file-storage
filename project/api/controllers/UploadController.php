<?php

namespace api\controllers;

use api\components\File;
use Yii;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

/**
 * @package api\controllers
 */
class UploadController extends Controller
{
    public function behaviors()
    {
        $behaviors =  parent::behaviors();

        unset($behaviors['rateLimiter']);
        unset($behaviors['authenticator']);

        return $behaviors;
    }

    /**
     * Upload files from $_FILES and $_POST['url] arrays
     * Return json answer with files names
     *
     * @param string $secret secret key (for auth)
     * @param string $project
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($secret, $project)
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
