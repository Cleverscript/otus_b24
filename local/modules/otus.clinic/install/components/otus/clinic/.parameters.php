<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\TypeTable;
use Bitrix\Iblock\IblockTable;
use Otus\Clinic\Helpers\IblockHelper;

if (!Loader::includeModule('iblock')) {
	return;
}

if (!Loader::includeModule('otus.clinic')) {
    return;
}
$iblockId = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_DOCTORS');

$pops = IblockHelper::getIblockProps($iblockId);
$arPropertys = $pops->getData();

echo '<pre>';
var_dump($arPropertys);
echo '<pre>';

$arComponentParameters = [
	'GROUPS' => [
	'LIST_SETTINGS' => [
		'NAME' => Loc::getMessage('T_GRID_LIST_SETTINGS'),
	],
	'DETAIL_SETTINGS' => [
		'NAME' => Loc::getMessage('T_GRID_DETAIL_SETTINGS'),
	],
		],
	'PARAMETERS' => [
		'VARIABLE_ALIASES' => [
			'ID' => ['NAME' => Loc::getMessage('T_GRID_COMPANY_ID_DESC')],
			'CODE' => ['NAME' => Loc::getMessage('T_GRID_COMPANY_CODE_DESC')],
		],
		'SEF_MODE' => [
			'detail' => [
				'NAME' => Loc::getMessage('T_GRID_DETAIL_URL_TEMPLATE'),
				'DEFAULT' => '#ID#/',
				'VARIABLES' => ['ID', 'CODE'],
			],
		],
		'LIST_FIELD_CODE' => CIBlockParameters::GetFieldCode(Loc::getMessage('T_GRID_IBLOCK_FIELD'), 'LIST_SETTINGS'),
		'LIST_PROPERTY_CODE' => [
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => Loc::getMessage('T_GRID_IBLOCK_PROPERTY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arPropertys,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'DETAIL_FIELD_CODE' => CIBlockParameters::GetFieldCode(Loc::getMessage('T_GRID_IBLOCK_FIELD'), 'DETAIL_SETTINGS'),
		'DETAIL_PROPERTY_CODE' => [
			'PARENT' => 'DETAIL_SETTINGS',
			'NAME' => Loc::getMessage('T_GRID_IBLOCK_PROPERTY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arPropertys,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'CACHE_TIME' => ['DEFAULT' => 86400],
	],
];