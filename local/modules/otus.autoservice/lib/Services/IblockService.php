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

    public static function getIblocks(): Result
    {
        $data = [];
        $result = new Result;

        $rows = IblockTable::getList([
            'filter' => [
                'IBLOCK_TYPE_ID' => 'lists',
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

    public function getIblockProperties(): Result
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
            $data[$row['CODE']] = $row['NAME'];
        }

        return $result->setData($data);
    }
}