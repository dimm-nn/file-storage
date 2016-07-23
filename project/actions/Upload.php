<?php

namespace app\actions;
use app\helpers\FileHelper;

/**
 * Class Upload
 * @package app\actions
 */
class Upload
{
    private $_project;

    /**
     * Upload files from $_FILES and $_POST['url] arrays
     * Return json answer with files hash names
     *
     * @param string $project project dir name
     * @param string $uploadToken secret token (for auth)
     */
    public function run($project, $uploadToken)
    {
        if (!in_array($uploadToken, \App::instance()->config['uploadTokens'])) {
            http_response_code(403);
            exit(0);
        }

        $this->_project = $project;

        $result = [];

        // Save files
        foreach ($_FILES as $name => $uploadedFile) {
            $result[$name] = $this->saveRaw($uploadedFile);
        }

        // Save files by url
        if ($urls = $_POST['urls'] ?? []) {
            $urlBlocks = array_chunk($urls, 7);

            foreach ($urlBlocks as $urlBlock) {
                $result = array_merge($result, $this->bulkLoad($urlBlock));
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Save file using $_FILES array item
     *
     * @param array $file
     * @return boolean|string false if has errors, uri on success upload.
     */
    public function saveRaw($file)
    {
        if (!empty($file['error'])
            || ($file['size'] <= 0)
            || !is_uploaded_file($file['tmp_name']))
        {
            return false;
        }

        return $this->save($file['tmp_name']);
    }

    /**
     * Save file by path
     *
     * @param string $filePath
     * @return mixed
     */
    private function save($filePath)
    {
        list($webPath, $fileAbsolutePath, $fileDir, $fileName) = $this->makePathData($filePath);

        if (is_file($fileAbsolutePath)) {
            return $webPath;
        }

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0775, true);
        }

        move_uploaded_file($filePath, $fileAbsolutePath);

        $fileLink = $fileDir . '/' . $fileName;
        if (!is_link($fileLink)) {
            symlink($fileAbsolutePath, $fileLink);
        }

        return $webPath;
    }

    /**
     * Download portion of files by url
     *
     * @param array $urls
     * @return array
     */
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

    /**
     * Make file info for save
     * 
     * @param string $fileName
     * @return array
     */
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

        $fileDirPath = STORAGE_DIR . '/' . $this->_project;

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
