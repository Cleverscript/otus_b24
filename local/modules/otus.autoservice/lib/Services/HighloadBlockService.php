<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock\HighloadBlockTable;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class HighloadBlockService
{
    use ModuleTrait;

    public int $entityHLBrandId;
    public int $entityHLModelId;
    public int $entityHLColorId;
    public int $entityHLProdGropId;

    public function __construct()
    {
        $this->entityHLBrandId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_BRAND");
        $this->entityHLModelId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_MODEL");
        $this->entityHLColorId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_CAR_COLOR");
        $this->entityHLProdGropId = Option::get(self::$moduleId, "OTUS_AUTOSERVICE_HL_PROD_GROUPS");
    }

    /**
     * Возвращает список справочников
     *
     * @param array $filter
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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

    /**
     * Возвращает список записей сущности справочник, по фильту
     *
     * @param Entity $entity
     * @param array $select
     * @param array $filter
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getItemsList(Entity $entity, array $select = [], array $filter = [])
    {
        return $entity->getDataClass()::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->fetchAll();
    }

    /**
     * Возвращает объект сущности справочника
     *
     * @param int $id
     * @return Entity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEntityHLById(int $id): Entity
    {
        $hlblock = HighloadBlockTable::getById($id)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    /**
     * Возвращает объект сущности справочника "Марка автомобиля"
     *
     * @return Entity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEntityHLBrand(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLBrandId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    /**
     * Возвращает объект сущности справочника "Модель автомобиля"
     *
     * @return Entity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEntityHLModel(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLModelId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    /**
     * Возвращает объект сущности справочника "Цвет автомобиля"
     *
     * @return Entity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEntityHLColor(): Entity
    {
        $hlblock = HighloadBlockTable::getById($this->entityHLColorId)->Fetch();

        return HighloadBlockTable::compileEntity($hlblock);
    }

    /**
     * Возвращает все данные элемента сущности справочника
     * с фильтрацией по UF_XML_ID
     *
     * @param string $xmlId
     * @param Entity $entity
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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
}
