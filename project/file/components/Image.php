<?php

namespace file\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;

/**
 * Class Image
 * @package common\helpers
 */
class Image extends Component
{
    public $uploadSecret;
    public $downloadSecret;

    public function init()
    {
        parent::init();

        if (empty($this->uploadSecret) || empty($this->downloadSecret)) {
            throw new Exception('You need set upload and download secrets');
        }
    }

    /**
     * @param $fileName
     * @return mixed|string
     */
    public function getDomain($fileName)
    {
        if (isset(Yii::$app->params['files']['host'])) {
            return str_replace(
                '{subdomain}',
                substr($fileName, 0, 1),
                Yii::$app->params['files']['host']
            );
        } else {
            return '/';
        }

    }

    /**
     * @param $src
     * @param array $params
     * @param array $options
     * @param null $default
     * @return string
     */
    public function img($src, $params = [], $options = [], $default = null)
    {
        if (empty($src)) {
            $src = $default;
        } else {
            $translit = '';
            if (isset($options['translit'])) {
                $translit = $options['translit'];
                unset($options['translit']);
            }

            $src = $this->absoluteUrl($src, $params, $translit);
        }

        if (isset($params['w']) && !isset($options['width'])) {
            $options['width'] = $params['w'];
        }

        if (isset($params['h']) && !isset($options['height'])) {
            $options['height'] = $params['h'];
        }

        return Html::img($src, $options);
    }

    /**
     * @param $src
     * @param array $params
     * @param string $translit
     * @param null $default
     * @return null|string
     */
    public function absoluteUrl($src, array $params = [], $translit = '', $default = null)
    {
        if (!$src) {
            return $default;
        }

        if (!Url::isRelative($src)) {
            return $src;
        }

        $pathInfo = pathinfo($src);
        $fileName = $pathInfo['filename'];

        if (!empty($params['f'])) {
            $pathInfo['extension'] = $params['f'];
            unset($params['f']);
        }

        ksort($params);

        $encodedParams = $this->encodeParams($params);

        $result = $this->getDomain($fileName).$fileName.'_'.$this->internalHash($src, $encodedParams).$encodedParams;

        if ($translit) {
            $result .= '/'.$translit;
        }

        if (!empty($pathInfo['extension'])) {
            $result .='.'.$pathInfo['extension'];
        }

        return $result;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function foursquareUrl($url, array $params = [])
    {
        if (!empty($params['w']) && !empty($params['h'])) {
            $url = str_replace('original', $params['w'] . 'x' . $params['h'], $url);
        } elseif (!empty($params['w'])) {
            $url = str_replace('original', 'width' . $params['w'], $url);
        } elseif (!empty($params['h'])) {
            $url = str_replace('original', 'height' . $params['h'], $url);
        }

        return $url;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function uploadByUrl($url)
    {
        $serviceUrl = Yii::$app->params['files']['uploadHost'] . $this->uploadSecret . '/' . Yii::$app->params['files']['project'];
        $post = http_build_query(['urls' => [$url]]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        return json_decode($response, true)[$url];
    }

    /**
     * @param array $params
     * @return string
     */
    private function encodeParams(array $params)
    {
        $result = '';
        foreach ($params as $key => $value) {
            $result .= '_'.$key.'-'.$value;
        }
        return $result;
    }

    /**
     * @param $filePath
     * @param $params
     * @return string
     */
    public function internalHash($filePath, $params)
    {
        $hash = hash(
            'crc32',
            $this->downloadSecret . $filePath . $params . $this->downloadSecret
        );

        return str_pad($this->internalBaseConvert($hash, 16, 36), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param $number
     * @param $fromBase
     * @param $toBase
     * @return int|string
     */
    public function internalBaseConvert($number, $fromBase, $toBase)
    {
        $str = trim($number);
        if (intval($fromBase) != 10) {
            $len = strlen($str);
            $q = 0;

            for ($i=0; $i<$len; $i++) {
                $r = base_convert($str[$i], $fromBase, 10);
                $q = bcadd(bcmul($q, $fromBase), $r);
            }
        } else {
            $q = $str;
        }

        if (intval($toBase) != 10) {
            $s = '';
            while (bccomp($q, '0', 0) > 0) {
                $r = intval(bcmod($q, $toBase));
                $s = base_convert($r, 10, $toBase) . $s;
                $q = bcdiv($q, $toBase, 0);
            }
        } else {
            $s = $q;
        }

        return $s;
    }

    /**
     * По uri-имени возвращает путь к файлу-оригиналу или false если он не найден.
     * @param string $webPath
     * @return string|boolean
     */
    public function resolvePhysicalPath($webPath)
    {
        $storagePath = Yii::getAlias('@storage') . '/';

        if (is_file($storagePath.$webPath))
            return $storagePath.$webPath;

        $pathInfo = pathinfo($webPath);
        $symlinkPath = $storagePath.$pathInfo['dirname'].'/'.$pathInfo['filename'];

        if (is_link($symlinkPath))
            return readlink($symlinkPath);

        return false;
    }

    /**
     * Создание и отдача изображения используя phpThumb
     * @param string $physicalPath путь к файлу-оригиналу.
     * @param array $params параметры нового изображения
     * @param string $project проект
     */
    public function generateImage($physicalPath, $params)
    {
        $imagick = new \Imagick($physicalPath);

        if (!empty($params['w']) || !empty($params['h'])) {
            $imagick->thumbnailImage(
                (int) ($params['w'] ?? 0),
                (int) ($params['h'] ?? 0)
            );
        }

        if (!isset($params['q']) && (isset($params['w']) || isset($params['h']))) {
            $params['q'] = 80;
        }

        Yii::$app->response->format = Response::FORMAT_RAW;

        $encoding = Yii::$app->charset;

        $info   = getimagesize($physicalPath);
        $mime   = $info['mime'];

        header("Content-Type: {$mime}; charset={$encoding}");

        echo $imagick->getImageBlob();
    }
}
