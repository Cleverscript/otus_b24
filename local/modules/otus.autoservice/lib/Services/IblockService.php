<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;
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
}