<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';

if ($siteID !== '') {
    define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Проверка сессии
 */
if (!check_bitrix_sessid()) {
    die();
}

Header('Content-Type: text/html; charset=' . LANG_CHARSET);

global $APPLICATION;
//$APPLICATION->ShowAjaxHead();

$request = Application::getInstance()->getContext()->getRequest();

$componentData = $request->get('PARAMS');

if(is_array($componentData)){
    $componentParams = isset($componentData['params']) && is_array($componentData['params']) ? $componentData['params'] : array();
}

$server = $request->getServer();

$ajaxLoaderParams = array(
    'url' => $server->get('REQUEST_URI'),
    'method' => 'POST',
    'dataType' => 'ajax',
    'data' => array('PARAMS' => $componentData)
);

$componentParams['AJAX_LOADER'] = $ajaxLoaderParams;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$APPLICATION->SetTitle(Loc::getMessage('OTUS_AUTOSERVICE_CAR_HISTORY_DEAL'));

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        'PLAIN_VIEW' => false,
        'USE_PADDING' => true,
        'POPUP_COMPONENT_NAME' => 'otus:autoservice.car_show',
        'POPUP_COMPONENT_TEMPLATE_NAME' => $componentData['template'] ?? '',
        'POPUP_COMPONENT_PARAMS' => $componentParams
    ]
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';

\CMain::FinalActions();