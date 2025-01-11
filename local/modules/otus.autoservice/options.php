<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Otus\Autoservice\Services\DealService;
use Otus\Autoservice\Services\IblockService;
use Otus\Autoservice\Services\HighloadBlockService;
use Otus\Clinic\Utils\BaseUtils;

$module_id = "otus.autoservice";

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

Loader::includeModule($module_id);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$defaultOptions = Option::getDefaults($module_id);

$iblocksLists = IblockService::getIblocks('lists');

if (!$iblocksLists->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($iblocksLists));
}

$iblocksCatalog = IblockService::getIblocks('CRM_PRODUCT_CATALOG');

if (!$iblocksCatalog->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($iblocksCatalog));
}

$hlblocks = (new HighloadBlockService)->getList();

if (!$hlblocks->isSuccess()) {
    CAdminMessage::ShowMessage(BaseUtils::extractErrorMessage($hlblocks));
}

$hlblArr = [];

foreach ($hlblocks->getData() as $hlblock) {
    $hlblArr[$hlblock['ID']] = "{$hlblock['NAME_LANG']} [{$hlblock['NAME']}]";
}

if ($iblockCarId = Option::get($module_id, 'OTUS_AUTOSERVICE_IB_CARS')) {
    $iblockService = new IblockService($iblockCarId);

    $iblockCarProps = $iblockService->getIblockProperties();
}

$dealService = new DealService();
$arDealCategories = $dealService->getCategories();

$dealPropsArr = [];
$dealProps = $dealService->getDealProps();
if ($dealProps->isSuccess()) {
    foreach ($dealProps->getData() as $dealProp) {
        $dealPropsArr[$dealProp['CODE']] = '[' .$dealProp['CODE'] . '] ' . $dealProp['NAME'];
    }
}

$arMainPropsTab = [
    "DIV" => "edit1",
    "TAB" => Loc::getMessage("OTUS_AUTOSERVICE_MAIN_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_AUTOSERVICE_MAIN_TAB_SETTINGS_TITLE"),
    "OPTIONS" => [
        [
            "OTUS_AUTOSERVICE_DEAL_CATEGORY",
            Loc::getMessage("OTUS_AUTOSERVICE_DEAL_CATEGORY"),
            null,
            ["selectbox", $arDealCategories]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS"),
            null,
            ["selectbox", $iblocksLists->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_REQUESTS",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_REQUESTS"),
            null,
            ["selectbox", $iblocksLists->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_PARTS",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_PARTS"),
            null,
            ["selectbox", $iblocksCatalog->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_HL_CAR_BRAND",
            Loc::getMessage("OTUS_AUTOSERVICE_HL_CAR_BRAND"),
            null,
            ["selectbox", $hlblArr]
        ],

        [
            "OTUS_AUTOSERVICE_HL_CAR_MODEL",
            Loc::getMessage("OTUS_AUTOSERVICE_HL_CAR_MODEL"),
            null,
            ["selectbox", $hlblArr]
        ],

        [
            "OTUS_AUTOSERVICE_HL_CAR_COLOR",
            Loc::getMessage("OTUS_AUTOSERVICE_HL_CAR_COLOR"),
            null,
            ["selectbox", $hlblArr]
        ],

    ]
];

$arCarPropsTab = [
    "DIV" => "edit2",
    "TAB" => Loc::getMessage("OTUS_AUTOSERVICE_IB_CAR_PROPS_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_AUTOSERVICE_IB_CAR_PROPS_TAB_SETTINGS"),
    "OPTIONS" => [

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_BRAND",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_BRAND"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_MODEL",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_MODEL"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_RELEASE_DATE",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_RELEASE_DATE"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_MILIAGE",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_MILIAGE"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_COLOR",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_COLOR"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_VIN",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_VIN"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

        [
            "OTUS_AUTOSERVICE_IB_CARS_PROP_CONTACT",
            Loc::getMessage("OTUS_AUTOSERVICE_IB_CARS_PROP_CONTACT"),
            null,
            ["selectbox", $iblockCarProps->getData()]
        ],

    ]
];

$dealPropsTab = [
    "DIV" => "edit3",
    "TAB" => Loc::getMessage("OTUS_AUTOSERVICE_DEAL_PROPS_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("OTUS_AUTOSERVICE_DEAL_PROPS_TAB_SETTINGS"),
    "OPTIONS" => [

        [
            "OTUS_AUTOSERVICE_DEAL_PROP_CAR",
            Loc::getMessage("OTUS_AUTOSERVICE_DEAL_PROP_CAR"),
            null,
            ["selectbox", $dealPropsArr]
        ],

    ]
];

$aTabs = [
    $arMainPropsTab,
    $arCarPropsTab,
    $dealPropsTab,
    [
        "DIV" => "edit4",
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