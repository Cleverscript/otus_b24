<?php
use Bitrix\Main\Loader;
use Otus\Customtab\Models;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class otus_autoservice
 */

if (class_exists("otus_autoservice")) return;

class otus_autoservice extends CModule
{
    public $MODULE_ID = "otus.autoservice";
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
        $this->MODULE_NAME = Loc::getMessage("OTUS_AUTOSERVICE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_AUTOSERVICE_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("OTUS_AUTOSERVICE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("OTUS_AUTOSERVICE_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";

        $this->eventManager = EventManager::getInstance();
        $this->localPath = $_SERVER["DOCUMENT_ROOT"] . '/local';
        $this->compPath = $_SERVER["DOCUMENT_ROOT"] . '/local/components';
    }

    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '20.00.00');
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

        return true;
    }

    function UnInstallFiles()
    {
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.list');
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.detail');
    }

    function InstallEvents()
    {
        $this->eventManager->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Handlers\\SidePanelHandler',
            'handleSidepanelLinks'
        );

        $this->eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Handlers\\TabHandler',
            'addTabs'
        );
    }

    function UnInstallEvents()
    {
        $this->eventManager->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Handlers\\SidePanelHandler',
            'handleSidepanelLinks'
        );

        $this->eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Handlers\\TabHandler',
            'updateTabs'
        );
    }

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
        global $APPLICATION;

        if ($this->isVersionD7()) {

            ModuleManager::registerModule($this->MODULE_ID);

            if (!$this->InstallFiles()) {
                return false;
            }
            if (!$this->InstallDB()) {
                return false;
            }

            $this->InstallEvents();

            return true;

        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_AUTOSERVICE_INSTALL_ERROR_VERSION'));
        }
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
            "reference_id" => ["D", "K", "S", "W"],
            "reference" => [
                "[D] " . Loc::getMessage("OTUS_AUTOSERVICE_RIGHT_DENIED"),
                "[K] " . Loc::getMessage("OTUS_AUTOSERVICE_RIGHT_READ"),
                "[S] " . Loc::getMessage("OTUS_AUTOSERVICE_RIGHT_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("OTUS_AUTOSERVICE_RIGHT_FULL")
            ]
        ];
    }
}