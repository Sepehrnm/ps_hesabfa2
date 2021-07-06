<?php

class LogService
{
    private static $fileName = _PS_MODULE_DIR_ . 'ps_hesabfa/' . "hesabfa-log.txt";

    public static function writeLogStr($logStr)
    {
        $file = fopen(self::$fileName, "a");
        fwrite($file, $logStr . "\n");
        fclose($file);
    }

    public static function writeLogObj($logObj)
    {
        ob_start();
        var_dump($logObj);
        file_put_contents(self::$fileName, PHP_EOL . ob_get_flush(), FILE_APPEND);
    }

    public static function readLog()
    {
        $file = fopen(self::$fileName, "r");
        $fileStr = fread($file, filesize(self::$fileName));
        fclose($file);
        return $fileStr;
    }
}