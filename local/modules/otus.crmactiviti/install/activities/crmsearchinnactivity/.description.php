<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("OTUS_CRM_SEARCH_INN_ACTIVITI_NAME"),
    "DESCRIPTION" => Loc::getMessage("OTUS_CRM_SEARCH_INN_ACTIVITI_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "СBPTestActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "Text" => [
            "NAME" => Loc::getMessage("OTUS_CRM_SEARCH_INN_ACTIVITI_IB_ELEM_INN"),
            "TYPE" => "string",
        ],
    ],
];