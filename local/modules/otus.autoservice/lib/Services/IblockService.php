<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class IblockService
{
    private int $iblockId;

    use ModuleTrait;

    public function __construct(int $iblockId)
    {
        $this->iblockId = $iblockId;
    }

    /**
     * Возвращает ID инфоблока эл-нта
     * @param int $elementId
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIblockIdByElement(int $elementId)
    {
        return ElementTable::getList([
            'select' => [
                'IBLOCK_ID',
            ],
            'filter' => ['ID' => $elementId],
        ])->fetch()['IBLOCK_ID'];
    }

    /**
     * Возвращает объект со списком всех инфоблоков системы
     * @param string $type
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIblocks(string $type): Result
    {
        $data = [];
        $result = new Result;

        $rows = IblockTable::getList([
            'filter' => [
                'IBLOCK_TYPE_ID' => $type,
            ],
            'select' => ['ID', 'NAME']
        ])->fetchAll();

        if (empty($rows)) {
            return $result->addError(new Error('Не удалось получить инфоблоки'));
        }

        foreach ($rows as $row) {
            $data[$row['ID']] = $row['NAME'];
        }

        return $result->setData($data);
    }

    /**
     * Возвращает объект с массивом всех св-ств инфоблока
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getIblockProperties(string $indexName = 'ID'): Result
    {
        $result = new Result;
        $data = [];

        $rows = PropertyTable::getList(
            [
                'filter' => ['IBLOCK_ID' => $this->iblockId],
                'select' => ['*']
            ]
        )->fetchAll();

        if (empty($rows)) {
            return $result->addError(
                Loc::getMessage('OTUS_AUTOSERVICE_IBLOCK_PROPS_EMPTY', ['#IBLOCK_ID#' => $this->iblockId])
            );
        }

        foreach ($rows as $row) {
            $data[$row[$indexName]] = $row['NAME'];
        }

        return $result->setData($data);
    }

    /**
     * Добавляет элемент инфоблока
     * @param $fields
     * @return Result
     */
    public function addIblockElement($fields): Result
    {
        $result = new Result;

        $el = new \CIBlockElement;

        if (!array_key_exists('IBLOCK_ID', $fields)) {
            $fields['IBLOCK_ID'] = $this->iblockId;
        }

        if ($id = $el->Add($fields)) {
            $result->setData(['ID' => $id]);
        } else {
            $result->addError(new Error($el->LAST_ERROR));
        }

        return $result;
    }
}