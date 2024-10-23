<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Otus\CrmActiviti\Helpers\IblockHelper;
use Otus\CrmActiviti\Utils\BaseUtils;

$module_id = "otus.crmactiviti";

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

Loader::includeModule($module_id);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$defaultOptions = Option::getDefaults($module_id);

$iblocks = IblockHelper::getIblocks();

if (!$iblocks->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($iblocks));
}

$orderIblId = Option::get($module_id , 'OTUS_CRM_ACTIVITI_ORDER_IBLOCK');
if ($orderIblId) {
    $pops = IblockHelper::getIblockProps($orderIblId);
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($pops));
    $arPropertys = $pops->getData();
}

$arMainPropsTab = [
    "DIV" => "edit1",
    "TAB" => Loc::getMessage("OTUS_CRM_ACTIVITI_MAIN_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_CRM_ACTIVITI_MAIN_TAB_SETTINGS_TITLE"),
    "OPTIONS" => [

        [
            "OTUS_CRM_ACTIVITI_ORDER_IBLOCK",
            Loc::getMessage("OTUS_CRM_ACTIVITI_ORDER_IBLOCK"),
            null,
            ["selectbox", $iblocks->getData()]
        ],

        [
            "OTUS_CRM_ACTIVITI_IBLOCK_PROP_INN",
            Loc::getMessage("OTUS_CRM_ACTIVITI_IBLOCK_PROP_INN"),
            null,
            ["selectbox", $arPropertys]
        ],

        [
            "OTUS_CRM_ACTIVITI_DADATA_TOKEN",
            Loc::getMessage("OTUS_CRM_ACTIVITI_DADATA_TOKEN"),
            null,
            ["text", 100]
        ],

        [
            "OTUS_CRM_ACTIVITI_DADATA_SECRET",
            Loc::getMessage("OTUS_CRM_ACTIVITI_DADATA_SECRET"),
            null,
            ["text", 100]
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
            __AdmSettingsSaveOptions($module_id, $aTab["OPTIONS"]);
        }
    }
}
?>

<!-- FORM TAB -->
<?php
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?php $tabControl->Begin(); ?>
<form method="post" action="<?=$APPLICATION->GetCurPage();?>?mid=<?=htmlspecialcharsbx($request["mid"]);?>&amp;lang=<?=LANGUAGE_ID?>" name="<?=$module_id;?>">
    <?php $tabControl->BeginNextTab(); ?>

    <?php
    foreach ($aTabs as $aTab) {
        if(is_array($aTab['OPTIONS'])) {
            __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
            $tabControl->BeginNextTab();
        }
    }
    ?>

    <?php require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php"); ?>

    <?php $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false)); ?>

    <?=bitrix_sessid_post();?>
</form>
<?php $tabControl->End(); ?>
<!-- X FORM TAB -->