<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = "otus.autoservice";

const DEPENDENCE_MODULE = [
    'im',
    'crm',
    'iblock',
    'catalog',
    'highloadblock'
];

$defaultOptions = Option::getDefaults($module_id);

Loc::loadMessages(__FILE__);

foreach (DEPENDENCE_MODULE as $module) {
    if (!Loader::includeModule($module)) {
        throw new \Exception(Loc::getMessage(
            "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
            ['#MODULE_ID#' => $module]
        ));
    }
}