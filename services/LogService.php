<?php

class LogService
{
    private static $fileName = _PS_MODULE_DIR_ . 'ps_hesabfa/' . "hesabfa-log.txt";

    public static function writeLogStr($logStr)
    {

        $dateTime = new DateTime('now', new DateTimeZone(Configuration::get('PS_TIMEZONE')));
        $date = $dateTime->format('[Y-m-d H:i:s] ');

        $logStr = $date . $logStr;

        $file = fopen(self::$fileName, "a");
        fwrite($file, $logStr . "\n");
        fclose($file);
    }

    public static function writeLogObj($logObj)
    {
        $dateTime = new DateTime('now', new DateTimeZone(Configuration::get('PS_TIMEZONE')));
        $date = $dateTime->format('[Y-m-d H:i:s] ');

        ob_start();
        echo $date;
        var_dump($logObj);
        file_put_contents(self::$fileName, PHP_EOL . ob_get_flush(), FILE_APPEND);
    }

    public static function readLog()
    {
        return file_get_contents(self::$fileName);
    }

    public static function clearLog() {
        if (file_exists(self::$fileName))
            file_put_contents(self::$fileName, "");
    }

    public static function getLogFilePath() {
        return self::$fileName;
    }
}