<?php

namespace app\interfaces;

interface FileWorker
{
    public function makeFile($path, $params = []);
}