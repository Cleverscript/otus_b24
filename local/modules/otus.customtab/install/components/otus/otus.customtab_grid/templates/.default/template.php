<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var $this CBitrixComponentTemplate
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

if ($arParams['SET_PAGE_TITLE'] == 'Y') {
    $APPLICATION->SetTitle(Loc::getMessage('OTUS_CUSTOMTAB_LIST_TITLE'));
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['ROWS'],
        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
        'ALLOW_SORT' => $arResult['ALLOW_SORT'],
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_HISTORY" => "N",
        "SHOW_ROW_CHECKBOXES" =>$arResult['SHOW_ROW_CHECKBOXES'],
        "SHOW_SELECTED_COUNTER" => false,
        "SHOW_PAGESIZE" => false,
    ]
);

/*
$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'titleflex',
	[
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'ROWS' => $arResult['ROWS'],
		'SORT' => $arResult['SORT'],
		'FILTER' => $arResult['FILTER'],
		'IS_EXTERNAL_FILTER' => false,
		'ENABLE_LIVE_SEARCH' => $arResult['ENABLE_LIVE_SEARCH'],
		'AJAX_ID' => '',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
		'AJAX_LOADER' => null,
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_NAVIGATION_PANEL'     => false,
        'PAGINATION' => $arResult['PAGINATION'],
	],
	$this->getComponent(),
	['HIDE_ICONS' => 'Y',]
);
*/