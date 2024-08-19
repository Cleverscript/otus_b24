<?php

// Composer autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    //require_once __DIR__ . '/../vendor/autoload.php';
}

// Bitrix autoload
\Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Otus\Helpers\CBExceptionLog' => "/local/classes/Otus/Helpers/CBExceptionLog.php",
]);

