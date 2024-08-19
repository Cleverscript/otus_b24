<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Otus\Helpers\CBExceptionLog;

/*try {*/

$i = 100;
echo $i / 0;

/*} catch (\Throwable $e) {
    $logger = new CBLog;
    $logger->initialize([]);

    echo '<pre>';
    var_dump($logger);
    echo '<pre>';

    $logger->write($e, 1);
}*/