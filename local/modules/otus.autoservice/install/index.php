<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Tables\BpCatalogProductsTable;

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
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.cars_grid');
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/' . str_replace('.', '/', $this->MODULE_ID) . '.car_show');
    }

    protected function getEventsArray()
    {
        return [
            ['iblock', 'OnStartIBlockElementAdd', '\\Otus\\Autoservice\\Handlers\\RequestHandler', 'onStartAdd'],
            ['iblock', 'OnStartIBlockElementAdd', '\\Otus\\Autoservice\\Handlers\\CarHandler', 'onStartAdd'],
            ['iblock', 'OnBeforeIBlockElementAdd', '\\Otus\\Autoservice\\Handlers\\CarHandler', 'beforeAdd'],
            ['iblock', 'OnIBlockPropertyBuildList', '\\Otus\\Autoservice\\Handlers\\HlblockPropertyBuildListHandler', 'GetUserTypeDescription'],

            ['crm', 'onEntityDetailsTabsInitialized', '\\Otus\\Autoservice\\Handlers\\TabHandler', 'addTabs'],
            ['crm', 'OnBeforeCrmDealAdd', '\\Otus\\Autoservice\\Handlers\\DealHandler', 'beforeAdd'],
            ['crm', 'OnAfterCrmDealAdd', '\\Otus\\Autoservice\\Handlers\\DealHandler', 'afterAdd'],
        ];
    }

    function InstallEvents()
    {
        foreach ($this->getEventsArray() as $row)
        {
            list($module, $event_name, $class, $function, $sort) = $row;
            $this->eventManager->RegisterEventHandler($module, $event_name, $this->MODULE_ID, $class, $function, $sort);
        }
        return true;
    }

    function UnInstallEvents()
    {
        foreach ($this->getEventsArray() as $row)
        {
            list($module, $event_name, $class, $function, ) = $row;
            $this->eventManager->UnRegisterEventHandler($module, $event_name, $this->MODULE_ID, $class, $function);
        }
        return true;
    }

    public function InstallAgents()
    {
        CAgent::AddAgent("\Otus\Autoservice\Agents\ActualizeQuantityAgent::run();", $this->MODULE_ID, "N", 60, "", "Y", "");
        return true;
    }

    function UnInstallAgents()
    {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    private function getEntities()
    {
        return [
            '\\' . BpCatalogProductsTable::class
        ];
    }

    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (!Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                Base::getInstance($entity)->createDbTable();
            }
        }

        return true;
    }

    public function UninstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        $connection = Application::getConnection();

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                $connection->dropTable($entity::getTableName());
            }
        }

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

            $this->InstallAgents();

            return true;

        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_AUTOSERVICE_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallAgents();
        $this->UnInstallFiles();
        $this->UninstallDB();

        ModuleManager::unRegisterModule($this->MODULE_ID);

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