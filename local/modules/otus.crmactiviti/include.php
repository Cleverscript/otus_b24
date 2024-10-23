<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

$module_id = "otus.crmactiviti";

$defaultOptions = Option::getDefaults($module_id);

Loader::registerAutoLoadClasses(null, [
    //'Otus\CrmActiviti\Helpers\IblockHelper' => "/local/modules/{$module_id}/lib/Helpers/IblockHelper.php",
    //'Otus\CrmActiviti\Utils\BaseUtils' => "/local/modules/{$module_id}/lib/Utils/BaseUtils.php",
]);