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
    public function testFailAuthenticate($host, $method, $url)
    {
        $response = $this->runApp($host, $method, $url);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @dataProvider failConfigProvider
     */
    public function testFailConfig($host, $method, $url)
    {
        $response = $this->runApp($host, $method, $url);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testFile($input_files, $host, $method, $url)
    {
        $files = File::moveFilesToTemp($input_files);
        foreach ($files as $key => $name) {
            $files[$key] = new UploadedFile($name);
        }
        $response = $this->runApp($host, $method, $url, [], $files);
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
            ['sub.example.ru', 'POST', '/upload/123'],
            ['sub.example-test2.ru', 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT'],
            ['sub.example-test2.ru', 'POST', '/upload/BMSnQraN3edkH9nBK98Gmmrz367JxWCT'],
        ];
    }

    /**
     * @return array
     */
    public function failConfigProvider()
    {
        return [
            ['', 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT'],
            ['sub.example2.ru', 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT'],
            ['sub.example-test3.ru', 'POST', '/upload/N3edBMSnQrakH9nBK98Gmmrz367JxWCT'],
        ];
    }

    /**
     * @return array
     */
    public function fileProvider()
    {
        return [
            [['di.png'], 'sub.example-test.ru', 'POST', '/upload/BMSnQraN3edkH9nBK98Gmmrz367JxWCT'],
            [['di.png','8307331.jpe'], 'sub.example-test.ru', 'POST', '/upload/BMSnQraN3edkH9nBK98Gmmrz367JxWCT'],
            [['napoleon for svg 1.svg','8307331.jpe'], 'sub.example-test2.ru', 'POST', '/upload/Gmmrz3BMSnQraN3edkH9nBK9867JxWCT'],
        ];
    }
}