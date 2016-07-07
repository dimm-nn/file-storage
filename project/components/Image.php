<?php

namespace app\components;

use app\helpers\FileHelper;
use Imagine\Filter\Transformation;
use Imagine\Gmagick\Imagine;
use Imagine\Image\Box;
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
    const DEFAULT_QUALITY = 85;

    public $uploadSecret;
    public $downloadSecret;

    /**
     * Make operation to image
     *
     * Available operations:
     * - w - generate thumbnail with width equal `w` (default - original)
     * - h - generate thumbnail with height equal `h` (default - original)
     * - q - quality of thumbnail (default - 85%)
     *
     * @param string $imagePath
     * @param array $params
     */
    public function makeImage($imagePath, $params)
    {
        $imagine = new Imagine;
        $transformation = new Transformation();
        $image = $imagine->open($imagePath);
        $options = [];

        $format = FileHelper::getExtension($imagePath);

        // Thumbnail
        if (!empty($params['w']) || !empty($params['h'])) {
            $box = new Box(
                (int) ($params['w'] ?? $params['h']),
                (int) ($params['h'] ?? $params['w'])
            );

            $transformation->thumbnail($box);
        }

        $quality = $params['q'] ?? self::DEFAULT_QUALITY;

        switch ($format) {
            case 'png':
                $options['png_compression_filter'] = ceil($quality / 10);
                break;
            case 'jpg':
            case 'jpeg':
            case 'pjpeg':
                $options['jpeg_quality'] = $quality;
                break;
        }

        Yii::$app->response->format = Response::FORMAT_RAW;

        $transformation->apply($image)->show($format, $options);
    }

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
     * @param $filePath
     * @param $params
     * @param $downloadSecret
     * @return string
     */
    public function internalHash($filePath, $params)
    {
        $hash = hash(
            'crc32',
            $this->downloadSecret . $filePath . $params . $this->downloadSecret
        );

        return str_pad(FileHelper::internalBaseConvert($hash, 16, 36), 5, '0', STR_PAD_LEFT);
    }
}
