<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Otus\Autoservice\Services\IblockService;
use Otus\Clinic\Utils\BaseUtils;

$module_id = "otus.autoservice";

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

Loader::includeModule($module_id);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$defaultOptions = Option::getDefaults($module_id);

$iblocks = IblockService::getIblocks();

if (!$iblocks->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($iblocks));
}

$arMainPropsTab = [
    "DIV" => "edit1",
    "TAB" => Loc::getMessage("OTUS_AUTOSERVICE_MAIN_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_AUTOSERVICE_MAIN_TAB_SETTINGS_TITLE"),
    "OPTIONS" => [

        [
            "OTUS_AUTOSERVICE_IB_CARS",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS"),
            null,
            ["selectbox", $iblocks->getData()]
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