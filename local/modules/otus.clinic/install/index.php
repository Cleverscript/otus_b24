<?php
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

/**
 * Class otus_clinic
 */

if (class_exists("otus_clinic")) return;

class otus_clinic extends CModule
{
    public $MODULE_ID = "otus.clinic";
    public $SOLUTION_NAME = "clinic";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_SORT;
    public $SHOW_SUPER_ADMIN_GROUP_RIGHTS;
    public $MODULE_GROUP_RIGHTS;

    public $eventManager;

    private string $localPath;
    private string $compPath;

    function __construct()
    {

        $arModuleVersion = array();
        include(__DIR__ . "/version.php");

        $this->exclusionAdminFiles = array(
            '..',
            '.'
        );

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("OTUS_CLINIC_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_CLINIC_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("OTUS_CLINIC_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("OTUS_CLINIC_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";

        $this->eventManager = EventManager::getInstance();
        $this->localPath = $_SERVER["DOCUMENT_ROOT"] . '/local';
        $this->compPath = $_SERVER["DOCUMENT_ROOT"] . '/local/components';
        $this->jsExtPath = $_SERVER["DOCUMENT_ROOT"] . '/local/js';

    }

    public function isVersionD7()
    {

        return CheckVersion(ModuleManager::getVersion("main"), "14.00.00");

    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {

            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));

        } else {

            return dirname(__DIR__);

        }
    }

    public static function getModuleId(): string
    {
        return basename(dirname(__DIR__));
    }

    public function getVendor(): string
    {
        $expl = explode('.', $this->MODULE_ID);
        return $expl[0];
    }

    function InstallFiles()
    {

        \CheckDirPath($this->localPath);
        \CheckDirPath($this->compPath);

        if (!CopyDirFiles(
            $this->GetPath() . '/install/admin',
            $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/', true)
        ) {

            return false;
        }

        if (!CopyDirFiles(
            $this->GetPath() . '/install/bitrix',
            $_SERVER["DOCUMENT_ROOT"] . '/bitrix/', true)
        ) {

            return false;
        }

        if (!Directory::isDirectoryExists($this->compPath)) {
            Directory::createDirectory($this->compPath);
        }

        if (!CopyDirFiles(
            $this->GetPath() . '/install/components',
            $this->compPath, true, true)
        ) {

            return false;
        }

        if (!CopyDirFiles(
            $this->GetPath() . '/install/js',
            $this->jsExtPath, true, true)
        ) {

            return false;
        }

        return true;
    }

    function UnInstallFiles()
    {
        //File::deleteFile($_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/itscript_qna_list.php');
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.list');
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.detail');
    }

    /**
     * Function register events solution
     */
    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            "iblock",
            "OnIBlockPropertyBuildList",
            $this->MODULE_ID,
            '\Otus\Clinic\UserTypes\BookingProcedureLink',
            "GetUserTypeDescription"
        );
    }

    /**
     * Function unregister events solution
     */
    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
    }

    // Create entity table in database
    public function InstallDB()
    {
        return true;
    }

    public function UninstallDB()
    {
        return true;
    }

    /**
     * Checking if dependent modules are installed
     * @param $module_id
     * @return bool
     */
    function checkIssetExtModules($module_id)
    {

        if (!Loader::includeModule($module_id)) {
            \CAdminMessage::ShowMessage(
                [
                    "MESSAGE" => GetMessage("ITSCRIPT_QNA_CHECK_ISS_MODULE_EXT_ERROR",
                        ["#MODULE_ID#" => $module_id]
                    ),
                    "DETAILS" => GetMessage("ITSCRIPT_QNA_CHECK_ISS_MODULE_EXT_ERROR_ALT",
                        ["#MODULE_ID#" => $module_id]
                    ),
                    "HTML" => true,
                    "TYPE" => "ERROR"
                ]
            );
            return false;
        }

        return true;
    }

    function DoInstall()
    {

        ModuleManager::registerModule($this->MODULE_ID);

        if (!$this->InstallFiles()) {
            return false;
        }
        if (!$this->InstallDB()) {
            return false;
        }

        $this->InstallEvents();

        return true;
    }

    function DoUninstall()
    {

        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UninstallDB();

        return true;
    }

    function GetModuleRightList()
    {
        return [
            "reference_id" => array("D", "K", "S", "W"),
            "reference" => [
                "[D] " . Loc::getMessage("ITSCRIPT_QNA_DENIED"),
                "[K] " . Loc::getMessage("ITSCRIPT_QNA_READ_COMPONENT"),
                "[S] " . Loc::getMessage("ITSCRIPT_QNA_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("ITSCRIPT_QNA_FULL")
            ]
        ];
    }
}