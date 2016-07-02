<?php

namespace api\components;

abstract class Controller extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors =  parent::behaviors();

        unset($behaviors['rateLimiter']);
        unset($behaviors['authenticator']);

        return $behaviors;
    }
}