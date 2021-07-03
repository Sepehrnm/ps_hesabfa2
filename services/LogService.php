<?php

namespace hesabfa\services;

interface ILogService {
    public function writeLogStr($logStr);
    public function writeLogObj($logObj);
    public function readLog();
}

class LogService implements ILogService
{
    private $fileName = _PS_MODULE_DIR_ . 'ps_hesabfa/' . "hesabfa-log.txt";

    public function writeLogStr($logStr)
    {
        $file = fopen($this->fileName, "a");
        fwrite($file, $logStr . "\n");
        fclose($file);
    }

    public function writeLogObj($logObj)
    {
        ob_start();
        var_dump($logObj);
        file_put_contents($this->fileName, PHP_EOL . ob_get_flush(), FILE_APPEND);
    }

    public function readLog()
    {
        $file = fopen($this->fileName, "r");
        $fileStr = fread($file, filesize($this->fileName));
        fclose($file);
        return $fileStr;
    }
}