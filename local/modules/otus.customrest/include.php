<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = "otus.customrest";

$defaultOptions = Option::getDefaults($module_id);

Loader::registerAutoLoadClasses(null, [
    //'Otus\' => "/local/modules/{$module_id}/lib/",
]);