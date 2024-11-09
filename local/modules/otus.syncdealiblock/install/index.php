<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class otus_syncdealiblock
 */

if (class_exists("otus_syncdealiblock")) return;

class otus_syncdealiblock extends CModule
{
    public $MODULE_ID = "otus.syncdealiblock";
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
        $this->MODULE_NAME = Loc::getMessage("OTUS_SYNCDEALIBLOCK_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_SYNCDEALIBLOCK_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("OTUS_SYNCDEALIBLOCK_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("OTUS_SYNCDEALIBLOCK_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";

        $this->eventManager = EventManager::getInstance();
        $this->localPath = $_SERVER["DOCUMENT_ROOT"] . '/local';
    }

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
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
        \CheckDirPath($this->activitiesPath);

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

        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function InstallEvents()
    {
        $this->eventManager->registerEventHandler(
            'iblock',
            'OnBeforeIBlockElementAdd',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'beforeAdd'
        );
        $this->eventManager->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'afterAdd'
        );
        $this->eventManager->registerEventHandler(
            'iblock',
            'OnBeforeIBlockElementUpdate',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'beforeUpdate'
        );
        $this->eventManager->registerEventHandler(
            'iblock',
            'OnBeforeIBlockElementDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'beforeDelete'
        );
        $this->eventManager->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'afterDelete'
        );

        $this->eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'afterAdd'
        );
        $this->eventManager->registerEventHandler(
            'crm',
            'OnBeforeCrmDealUpdate',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'beforeUpdate'
        );
        $this->eventManager->registerEventHandler(
            'crm',
            'OnBeforeCrmDealDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'beforeDelete'
        );
        $this->eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'afterDelete'
        );
    }

    function UnInstallEvents()
    {
        $this->eventManager->unRegisterEventHandler(
                'iblock',
                'OnBeforeIBlockElementAdd',
                $this->MODULE_ID,
                '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
                'beforeAdd'
            );
        $this->eventManager->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'afterAdd'
        );
        $this->eventManager->unRegisterEventHandler(
            'iblock',
            'OnBeforeIBlockElementUpdate',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'beforeUpdate'
        );
        $this->eventManager->unRegisterEventHandler(
            'iblock',
            'OnBeforeIBlockElementDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'beforeDelete'
        );
        $this->eventManager->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\IblockHandler',
            'afterDelete'
        );

        $this->eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'afterAdd'
        );
        $this->eventManager->unRegisterEventHandler(
            'crm',
            'OnBeforeCrmDealUpdate',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'beforeUpdate'
        );
        $this->eventManager->unRegisterEventHandler(
            'crm',
            'OnBeforeCrmDealDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'beforeDelete'
        );
        $this->eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealDelete',
            $this->MODULE_ID,
            '\\Otus\\SyncDealIblock\\Handlers\\DealHandler',
            'afterDelete'
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
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_SYNCDEALIBLOCK_INSTALL_ERROR_VERSION'));
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
            "reference_id" => ["D", "R", "S", "W"],
            "reference" => [
                "[D] " . Loc::getMessage("OTUS_SYNCDEALIBLOCK_DENIED"),
                "[R] " . Loc::getMessage("OTUS_SYNCDEALIBLOCK_READ"),
                "[S] " . Loc::getMessage("OTUS_SYNCDEALIBLOCK_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("OTUS_SYNCDEALIBLOCK_FULL")
            ]
        ];
    }
}