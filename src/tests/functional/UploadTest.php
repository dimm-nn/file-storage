<?php


namespace Tests\functional;

use Slim\Http\UploadedFile;
use Tests\helpers\File;

/**
 * Class UploadTestCase
 * @package Tests\Functional
 */
class UploadTest extends BaseTestCase
{

    public function testFailAuthenticate()
    {
        $response = $this->runApp('POST', '/upload/example/123');
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testFailConfig()
    {
        $response = $this->runApp('POST', '/upload/example2/N3edBMSnQrakH9nBK98Gmmrz367JxWCT');

        $this->assertEquals(400, $response->getStatusCode());
    }


    public function testFile()
    {

        $files = File::moveFilesToTemp([
            'di.png'
        ]);
        foreach ($files as $key => $name) {
            $files[$key] = new UploadedFile($name);
        }
        $response = $this->runApp('POST', '/upload/example/N3edBMSnQrakH9nBK98Gmmrz367JxWCT', [], $files);
        $response->getBody()->rewind();
        $data = json_decode($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(count($files), count($data));
    }
}
