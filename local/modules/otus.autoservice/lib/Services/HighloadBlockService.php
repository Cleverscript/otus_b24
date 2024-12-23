<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable;
use Otus\Autoservice\Traits\ModuleTrait;

class HighloadBlockService
{
    use ModuleTrait;
    public function __construct()
    {
        $this->includeModules();

        $this->entityHLBrandId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_BRAND");
        $this->entityHLModelId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_MODEL");
        $this->entityHLColorId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_COLOR");
    }

    public function getList(): Result
    {
        $result = new Result;

        $rows = HighloadBlockTable::query()
           ->setSelect(['*'])
           ->fetchAll();

        if (empty($rows)) {
            return $result->addError(new Error(Loc::loadMessages('OTUS_AUTOSERVICE_HIGHLOAD_BLOCK_NULL')));
        }

        return $result->setData($rows);
    }

    public function getEntityHLBrand()
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLBrandId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getEntityHLModel()
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLModelId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getEntityHLColor()
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLColorId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    private function includeModules(): void
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new \Exception(Loc::getMessage(
                "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
                ['#MODULE_ID#' => 'otus.autoservice']
            ));
        }
    }
}