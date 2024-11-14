<?php

namespace Otus\Bookingfield\Helpers;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\IblockTable;

class IblockHelper
{
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
            return $result->addError(new Error(Loc::getMessage('OTUS_BOOKINGFIELD_IBLOCKS_EMPTY')));
        }

        foreach ($rows as $row) {
            $data[$row['ID']] = $row['NAME'];
        }

        return $result->setData($data);
    }

    public static function getIblockProps(int $iblockId): Result
    {
        $data = [];
        $result = new Result;

        $rsProp = \CIBlockProperty::GetList(
            [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $iblockId,
            ]
        );

        while ($arr = $rsProp->Fetch()) {
            if (in_array($arr['PROPERTY_TYPE'], ['L', 'N', 'S', 'E'])) {
                $data[$arr['ID']] = '[' . $arr['CODE'] . '] ' . $arr['NAME'];
            }
        }

        return $result->setData($data);
    }
}