<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Otus\Bookingfield\Helpers\IblockHelper;
use Otus\Bookingfield\Utils\BaseUtils;

global $APPLICATION;
$moduleId = "otus.gettstart";

require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/local/modules/{$moduleId}/include.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/local/modules/{$moduleId}/prolog.php";

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight($moduleId);

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule($moduleId);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$defaultOptions = Option::getDefaults($moduleId);

$arMainPropsTab = [
    "DIV" => "edit1",
    "TAB" => Loc::getMessage("OTUS_GETTSTART_MAIN_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_GETTSTART_MAIN_TAB_SETTINGS_TITLE"),
    "OPTIONS" => [

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
require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php";

//Save form
if ($request->isPost() && $request["save"] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        if (!empty($aTab['OPTIONS'])) {
            __AdmSettingsSaveOptions($moduleId, $aTab["OPTIONS"]);
        }
    }
}

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

    <?php $tabControl->Buttons(['btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false]); ?>

    <?=bitrix_sessid_post();?>
</form>
<?php $tabControl->End(); ?>

<?php require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"; ?>

