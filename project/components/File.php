<?php

namespace app\components;

use app\helpers\FileHelper;

class File
{
    private $_projectName;

    public function __construct($project)
    {
        $this->_projectName = $project;
    }

    /**
     * Save file by path
     * "/storage/{projectName}/firstDir/secondDir/../{fileName}.{Extension}"
     * Also crete symlink on file without ext
     * "storage/{projectName}/firstDir/secondDir/../{fileName}" на файл.
     *
     * @param string $filePath
     * @return boolean|string false if has errors, uri on success upload.
     */
    public function saveRaw($filePath)
    {
        if (!empty($filePath['error'])
            || ($filePath['size'] <= 0)
            || !is_uploaded_file($filePath['tmp_name']))
        {
            return false;
        }

        return $this->save($filePath['tmp_name']);
    }

    private function save($tempFile)
    {
        list($webPath, $fileAbsolutePath, $fileDir, $fileName) = $this->makePathData($tempFile);

        if (is_file($fileAbsolutePath)) {
            return $webPath;
        }

        $this->mkDir($fileDir);

        move_uploaded_file($tempFile, $fileAbsolutePath);

        $fileLink = $fileDir . '/' . $fileName;
        if (!is_link($fileLink)) {
            symlink($fileAbsolutePath, $fileLink);
        }

        return $webPath;
    }

    public function bulkLoad($urls)
    {
        $multi = curl_multi_init();

        $handles = [];
        foreach ($urls as $url)
        {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => false,
//                CURLOPT_USERAGENT      => '',
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);

            $handles[$url] = $ch;
            curl_multi_add_handle($multi, $ch);
        }

        $running = count($urls);
        do
        {
            usleep(25000);
            $res = curl_multi_exec($multi, $running);
        } while (($running > 0) || ($res == CURLM_CALL_MULTI_PERFORM));

        $results = [];

        foreach ($handles as $url => $handle)
        {
            $fileContent = (string)curl_multi_getcontent($handle);

            if (empty($fileContent) || (curl_getinfo($handle, CURLINFO_HTTP_CODE) >= 400)) {
                $results[basename($url)] = false;
            } else {

                $tempFile = '/tmp/' . tmpfile();

                file_put_contents($tempFile, $fileContent);

                $results[basename($url)] = $this->save($tempFile);
            }

            curl_multi_remove_handle($multi, $handle);
            curl_close($handle);
        }

        curl_multi_close($multi);

        return $results;
    }

    private function makePathData($fileName)
    {
        static $nameLength = 13;
        static $shaOffset = 0;

        $extension = FileHelper::getExtension($fileName);

        $sha = sha1_file($fileName);

        $shaBase36 = FileHelper::internalBaseConvert($sha, 16, 36);
        $webName   = substr($shaBase36, $shaOffset, $nameLength);

        if (strlen($webName) < $nameLength) {
            $webName = str_pad($webName, $nameLength, '0', STR_PAD_LEFT);
        }

        $fileDirPath = STORAGE_DIR . '/' . $this->_projectName;

        $fileParts = FileHelper::splitNameIntoParts($webName);
        $fileName = end($fileParts);
        unset($fileParts[count($fileParts) - 1]);

        foreach ($fileParts as $partItem) {
            $fileDirPath .= '/' . $partItem;
        }

        $fileAbsolutePath = $fileDirPath . '/' . $fileName . '.' . $extension;

        $webName = $webName . '.' . $extension;

        return [
            $webName,
            $fileAbsolutePath,
            $fileDirPath,
            $fileName
        ];
    }

    private function mkDir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}