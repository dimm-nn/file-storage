<?php

namespace app\controllers;

use app\components\Controller;
use app\helpers\FileHelper;
use app\helpers\Helper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * @package controllers
 */
class ImageController extends Controller
{
    /**
     * Generate new image by params.
     *
     * @param string $file uri-имя исходного (физического) файла
     * @param string $hash контрольная сумма uri физического файла и параметров
     * @param string $extension расширение (формат) создаваемого файла
     * @param string $params дополнительные параметры конвертации
     * @param null $translit
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionGenerate($file, $hash, $extension, $params = '', $translit = null)
    {
        $project = $_SERVER['DOMAIN'];

        $hashPath = $file . '.' . $extension;

        $nameParts = FileHelper::splitNameIntoParts($file);

        $pathPrefix = $project . '/' . implode('/', $nameParts);
        $filePath = $pathPrefix.'.'.$extension;

        if (Yii::$app->image->internalHash($hashPath, $params) !== $hash) {
            throw new BadRequestHttpException();
        }

        $physicalPath = Yii::$app->image->resolvePhysicalPath($filePath);

        if (!$physicalPath) {
            throw new NotFoundHttpException;
        }

        $physicalExtension = pathinfo($physicalPath, PATHINFO_EXTENSION);

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        if (in_array($extension, $extensions) && in_array($physicalExtension, $extensions)) {
            $thumbParams = Helper::internalDecodeParams($params);
            $thumbParams['f'] = $extension;

            Yii::$app->image->makeImage($physicalPath, $thumbParams);
        } elseif ($extension == $physicalExtension) {
            Yii::$app->response->sendFile(basename($filePath), file_get_contents($physicalPath));
        }
        else {
            throw new BadRequestHttpException();
        }
    }
}
