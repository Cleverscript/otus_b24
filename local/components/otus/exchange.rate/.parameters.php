<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyLangTable;

if (!Loader::includeModule('currency')) {
	return;
}

$arCurrency = [];

$rows = CurrencyLangTable::query()
            ->setSelect(['CURRENCY', 'FULL_NAME'])
            ->exec();

foreach ($rows as $row) {
    $arCurrency[$row['CURRENCY']] = $row['FULL_NAME'];
}

$arComponentParameters = [
	'GROUPS' => [],
	'PARAMETERS' => [
        "CURRENCY_FROM" => Array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("T_CURRENCY_FROM"),
            "TYPE" => "LIST",
            "DEFAULT" => "SORT",
            "VALUES" => $arCurrency,
            "ADDITIONAL_VALUES" => "N",
        ),
		'CACHE_TIME' => ['DEFAULT' => 86400],
	],
];