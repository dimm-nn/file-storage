<?php

namespace app\components;

use app\helpers\FileHelper;
use Yii;
use yii\base\Object;

class FileSaver extends Object
{
    public $projectName;

    public function save()
    {
        $files = $this->saveFiles();

        if ($url = Yii::$app->request->get('urls', [])) {
            $files = array_merge($files, $this->uploadFiles($url));
        }

        return $files;
    }

    public function saveFiles()
    {
        $files = [];

        foreach ($_FILES as $uploadedName => $uploadedFile)
        {
            $webPath = $this->saveFile($uploadedName, $uploadedFile);
            $files[] = $webPath;
        }

        return $files;
    }

    /**
     * Save file by path
     * "/storage/{projectName}/firstDir/secondDir/../{fileName}.{Extension}"
     * Also crete symlink on file without ext
     * "storage/{projectName}/firstDir/secondDir/../{fileName}" на файл.
     *
     * @param string $fileName
     * @param string $filePath
     * @return boolean|string false if has errors, uri on success upload.
     */
    private function saveFile($fileName, $filePath)
    {
        if (!empty($filePath['error'])
            || ($filePath['size'] <= 0)
            || !is_uploaded_file($filePath['tmp_name']))
        {
            return false;
        }

        if (($extensionStart = strrpos($fileName, '_')) !== false) {
            $extension = substr($fileName, $extensionStart + 1);
        } else {
            $extension = FileHelper::getExtension($filePath['tmp_name']);
        }

        $sha = sha1_file($filePath['tmp_name']);

        $newFileName = $this->makeNewFileName($sha, $extension);

        list($webPath, $fileAbsolutePath, $fileDir, $fileName) = $newFileName;

        if (is_file($fileAbsolutePath)) {
            return $webPath;
        }

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0775, true);
        }

        move_uploaded_file($filePath['tmp_name'], $fileAbsolutePath);

        $fileLink = $fileDir . '/' . $fileName;
        if (!is_link($fileLink)) {
            symlink($fileAbsolutePath, $fileLink);
        }

        return $webPath;
    }

    /**
     * Save files by urls
     *
     * @param $urls
     * @return array
     */
    private function uploadFiles($urls)
    {
        $urlBlocks = array_chunk($urls, 7);

        $results = [];
        foreach ($urlBlocks as $urlBlock)
        {
            $results = array_merge($results, $this->bulkLoad($urlBlock));
        }

        return $results;
    }

    private function bulkLoad($urls)
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

            if (empty($fileContent) || (curl_getinfo($handle, CURLINFO_HTTP_CODE) >= 400))
            {
                $results[$url] = false;
            }
            else
            {
                $results[$url] = $this->saveUploadedFile($url, $fileContent);
            }

            curl_multi_remove_handle($multi, $handle);
            curl_close($handle);
        }

        curl_multi_close($multi);

        return $results;
    }

    private function saveUploadedFile($url, $fileContent)
    {
        $tempFile = Yii::getAlias('application.runtime').DIRECTORY_SEPARATOR.uniqid('_upload').pathinfo($url, PATHINFO_EXTENSION);
        file_put_contents($tempFile, $fileContent);

        $extension = FileHelper::getExtension($tempFile);

        $sha = sha1($fileContent);
        list($webPath, $physicalPath, $storageDir, $storageName) = $this->makeNewFileName($sha, $extension);

        if (is_file($physicalPath))
        {
            unlink($tempFile);
            return $webPath;
        }

        if (!is_dir($storageDir))
            mkdir($storageDir, 0775, true);

        rename($tempFile, $physicalPath);

        if (!is_link($storageDir.$storageName))
            symlink($physicalPath, $storageDir.$storageName);

        return $webPath;
    }

    public function makeNewFileName($sha, $extension)
    {
        static $nameLength = 13;
        static $shaOffset = 0;

        $shaBase36 = FileHelper::internalBaseConvert($sha, 16, 36);
        $webName   = substr($shaBase36, $shaOffset, $nameLength);

        if (strlen($webName) < $nameLength) {
            $webName = str_pad($webName, $nameLength, '0', STR_PAD_LEFT);
        }

        $fileDirPath = Yii::getAlias('@storage') . '/' . $this->projectName;

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
}