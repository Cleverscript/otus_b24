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
 * Class otus_bookingfield
 */

if (class_exists("otus_bookingfield")) return;

class otus_bookingfield extends CModule
{
    public $MODULE_ID = "otus.bookingfield";
    public $SOLUTION_NAME = "bookingfield";
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
        $this->MODULE_NAME = Loc::getMessage("OTUS_BOOKINGFIELD_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_BOOKINGFIELD_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("OTUS_BOOKINGFIELD_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("OTUS_BOOKINGFIELD_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";

        $this->eventManager = EventManager::getInstance();
        $this->localPath = $_SERVER["DOCUMENT_ROOT"] . '/local';
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
        Directory::deleteDirectory($this->jsExtPath . '/otus');
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
            '\Otus\Bookingfield\UserTypes\BookingProcedureLink',
            "GetUserTypeDescription"
        );

        $eventManager->registerEventHandler(
            "main",
            "OnPageStart",
            $this->MODULE_ID,
            '\Otus\Bookingfield\Helpers\ExtensionHelper',
            "getExtensions"
        );
    }

    /**
     * Function unregister events solution
     */
    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            "iblock",
            "OnIBlockPropertyBuildList",
            $this->MODULE_ID,
            '\Otus\Bookingfield\UserTypes\BookingProcedureLink',
            "GetUserTypeDescription"
        );

        $eventManager->unRegisterEventHandler(
            "main",
            "OnPageStart",
            $this->MODULE_ID,
            '\Otus\Bookingfield\Helpers\ExtensionHelper',
            "getExtensions"
        );
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

    function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        if (!$this->InstallFiles()) {
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

        return true;
    }

    function GetModuleRightList()
    {
        return [
            "reference_id" => array("D", "K", "S", "W"),
            "reference" => [
                "[D] " . Loc::getMessage("OTUS_BOOKINGFIELD_DENIED"),
                "[K] " . Loc::getMessage("OTUS_BOOKINGFIELD_READ"),
                "[S] " . Loc::getMessage("OTUS_BOOKINGFIELD_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("OTUS_BOOKINGFIELD_FULL")
            ]
        ];
    }
}