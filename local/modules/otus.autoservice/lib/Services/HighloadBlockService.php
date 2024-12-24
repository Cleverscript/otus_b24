<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

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

    public function getList(array $filter = []): Result
    {
        $result = new Result;

        $rows = HighloadBlockTable::query()
            ->setSelect(['*', 'NAME_LANG' => 'LANG.NAME'])
            ->setFilter($filter)
            ->fetchAll();

        if (empty($rows)) {
            return $result->addError(new Error(Loc::loadMessages('OTUS_AUTOSERVICE_HIGHLOAD_BLOCK_NULL')));
        }

        return $result->setData($rows);
    }

    public function getItemsList(Entity $entity, array $select = [], array $filter = [])
    {
        return $entity->getDataClass()::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->fetchAll();
    }

    public function getEntityHLById(int $id): Entity
    {
        $hlblock = HighloadBlockTable::getById($id)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getEntityHLBrand(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLBrandId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getEntityHLModel(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLModelId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getEntityHLColor(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLColorId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    public function getHLItemByXmlId(string $xmlId, Entity $entity): array
    {
        if (!$entity) {
            throw new \InvalidArgumentException(
                Loc::loadMessages('OTUS_AUTOSERVICE_HIGHLOAD_BLOCK_ENTITY_NULL')
            );
        }

        return $entity->getDataClass()::query()
            ->where('UF_XML_ID', $xmlId)
            ->setSelect(['*'])
            ->fetch();
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