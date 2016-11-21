<?php

declare(strict_types=1);

namespace Tests\functional;

use Slim\Http\UploadedFile;
use Tests\helpers\File;

/**
 * Class UploadTestCase
 * @package Tests\Functional
 */
class UploadTest extends BaseTestCase
{
    /**
     * @dataProvider failAuthenticateProvider
     */
    public function testFailAuthenticate($method, $url)
    {
        $response = $this->runApp($method, $url);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @dataProvider failConfigProvider
     */
    public function testFailConfig($method, $url)
    {
        $response = $this->runApp($method, $url);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testFile($input_files, $method, $url)
    {
        $files = File::moveFilesToTemp($input_files);
        foreach ($files as $key => $name) {
            $files[$key] = new UploadedFile($name);
        }
        $response = $this->runApp($method, $url, [], $files);
        $response->getBody()->rewind();
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(count($files), count($data));
    }

    /**
     * @return array
     */
    public function failAuthenticateProvider()
    {
        return [
            ['POST', '/upload/123?domain=example'],
            ['POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT?domain=example-test2'],
            ['POST', '/upload/BMSnQraN3edkH9nBK98Gmmrz367JxWCT?domain=example-test2'],
        ];
    }

    /**
     * @return array
     */
    public function failConfigProvider()
    {
        return [
            ['POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT'],
            ['POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT?domain=example2'],
            ['POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT?domain=example-test3'],
        ];
    }

    /**
     * @return array
     */
    public function fileProvider()
    {
        return [
            [['di.png'], 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT?domain=example'],
            [['di.png','8307331.jpe'], 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT?domain=example'],
            [['napoleon for svg 1.svg','8307331.jpe'], 'POST', '/upload/Gmmrz3BMSnQraN3edkH9nBK9867JxWCT?domain=example-test2'],
        ];
    }
}