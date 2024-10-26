<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Otus\SyncDealIblock\Helpers\IblockHelper;
use Otus\SyncDealIblock\Helpers\DealHelper;
use Otus\SyncDealIblock\Utils\BaseUtils;

$moduleId = 'otus.syncdealiblock';

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

Loader::includeModule($moduleId);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$defaultOptions = Option::getDefaults($moduleId);

$iblocks = IblockHelper::getIblocks();

if (!$iblocks->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($iblocks));
}

$arIblockPropertys = [];
$orderIblId = Option::get($moduleId , 'OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK');
if ($orderIblId) {
    $pops = IblockHelper::getIblockProps($orderIblId);
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($pops));
    $arIblockPropertys = $pops->getData();
}

$arDealPropertys = [];
$pops = DealHelper::getDealProps()->getData();
if (!empty($pops)) {
    foreach ($pops as $prop) {
        $arDealPropertys[$prop['CODE']] = "[{$prop['CODE']}] {$prop['NAME']}";
    }
}

$arMainPropsTab = [
    "DIV" => "edit1",
    "TAB" => Loc::getMessage("OTUS_SYNCDEALIBLOCK_MAIN_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_SYNCDEALIBLOCK_MAIN_TAB_SETTINGS_TITLE"),
    "OPTIONS" => [

        [
            "OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK",
            Loc::getMessage("OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK"),
            null,
            ["selectbox", $iblocks->getData()]
        ],

        [
            "OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_DEAL_CODE",
            Loc::getMessage("OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_DEAL_CODE"),
            null,
            ["selectbox", $arIblockPropertys]
        ],

        [
            "OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_SUM_CODE",
            Loc::getMessage("OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_SUM_CODE"),
            null,
            ["selectbox", $arIblockPropertys]
        ],

        [
            "OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_ASSIGNED_CODE",
            Loc::getMessage("OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_ASSIGNED_CODE"),
            null,
            ["selectbox", $arIblockPropertys]
        ],

        [
            "OTUS_SYNCDEALIBLOCK_CRM_DEAL_PROP_UF_ORDER",
            Loc::getMessage("OTUS_SYNCDEALIBLOCK_CRM_DEAL_PROP_UF_ORDER"),
            null,
            ["selectbox", $arDealPropertys]
        ],

    ]
];

$aTabs = [
    $arMainPropsTab,
    [
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ],
];
?>

<?php
//Save form
if ($request->isPost() && $request["save"] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        if (!empty($aTab['OPTIONS'])) {
            __AdmSettingsSaveOptions($moduleId, $aTab["OPTIONS"]);
        }
    }
}
?>

<!-- FORM TAB -->
<?php
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?php $tabControl->Begin(); ?>
<form method="post" action="<?=$APPLICATION->GetCurPage();?>?mid=<?=htmlspecialcharsbx($request["mid"]);?>&amp;lang=<?=LANGUAGE_ID?>" name="<?=$moduleId;?>">
    <?php $tabControl->BeginNextTab(); ?>

    <?php
    foreach ($aTabs as $aTab) {
        if(is_array($aTab['OPTIONS'])) {
            __AdmSettingsDrawList($moduleId, $aTab['OPTIONS']);
            $tabControl->BeginNextTab();
        }
    }
    ?>

    <?php //require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php"; ?>

    <?php $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false)); ?>

    <?=bitrix_sessid_post();?>
</form>
<?php $tabControl->End(); ?>
<!-- X FORM TAB -->