<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Otus\Customrest\Tables\CarTable;

Loc::loadMessages(__FILE__);

/**
 * Class otus_customrest
 */

if (class_exists("otus_customrest")) return;

class otus_customrest extends CModule
{
    public $MODULE_ID = "otus.customrest";
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
        $this->MODULE_NAME = Loc::getMessage("OTUS_CUSTOMREST_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_CUSTOMREST_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("OTUS_CUSTOMREST_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("OTUS_CUSTOMREST_PARTNER_URI");

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

    }

    private function getEntities()
    {
        return [
            '\\' . CarTable::class
        ];
    }

    function InstallEvents()
    {
        $this->eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\\Otus\\Customrest\\Handlers\\CarRestHandler',
            'carRestMethodsRegistration'
        );
    }

    function UnInstallEvents()
    {
        $this->eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\\Otus\\Customrest\\Handlers\\CarRestHandler',
            'carRestMethodsRegistration'
        );
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

            return true;

        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_CUSTOMREST_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        $this->UninstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function GetModuleRightList()
    {
        return [
            "reference_id" => ["D", "K", "S", "W"],
            "reference" => [
                "[D] " . Loc::getMessage("OTUS_CUSTOMREST_DENIED"),
                "[K] " . Loc::getMessage("OTUS_CUSTOMREST_READ_COMPONENT"),
                "[S] " . Loc::getMessage("OTUS_CUSTOMREST_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("OTUS_CUSTOMREST_FULL")
            ]
        ];
    }
}