<?php

namespace Tests\unit;

use app\components\storage\File;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class FileTest
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

    private $_filesystem;

    public function setUp()
    {
        $adapter = new Local('/storage/unit-example-test');
        $this->_filesystem = new Filesystem($adapter);
    }

    /**
     * @param string $filename
     * @param string $path
     * @dataProvider fileProvider
     */
    public function testUpload($filename, $path)
    {
        $file = new File($this->_filesystem);
        $file->upload($filename);
        $this->assertEquals($file->getPath(), $path);
        $this->assertEquals(sha1_file($filename), sha1($file->getContent()));
    }


    /**
     * @return array
     */
    public function fileProvider()
    {
        $files = [
            ['8307331.jpe', 'a2/s1/a2s11i90y2ixk.jpeg'],
            ['di.png', 'r6/06/r606m0z5ygvgd.png'],
            ['napoleon for svg 1.svg', 'fz/lo/fzlohhh132w6i.svg'],
            ['свободу сократу.png', 'q4/g7/q4g7g0h98m9t8.png'],
        ];
        $result = [];
        foreach ($files as $file) {
            $tmpName = \Tests\helpers\File::moveFileToTemp($file[0]);
            $result[] = [$tmpName, $file[1]];
        }

        return $result;
    }

}
