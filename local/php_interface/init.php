<?php

// Composer autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

//\Bex\Monolog\MonologAdapter::loadConfiguration();

// Bitrix autoload
\Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Otus\Helpers\CBExceptionLog' => "/local/classes/Otus/Helpers/CBExceptionLog.php",
]);

/**
 * Сохраняет входящие данные в файл лога
 * @param type mixed $data
 */
function pLog($data='',$logFileName="pLog.txt"){
    $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/local/logs/".$logFileName, "a");
    fwrite($fp, "=================================\r\n" . date('d.m.Y H:i:s') . "\r\n" . print_r($data,true) . "\r\n");
    fclose($fp);
}
