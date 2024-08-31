<?php

namespace Otus\Clinic\Helpers;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Iblock\IblockTable;
use Otus\Clinic\Utils\BaseUtils;


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
            return $result->addError(new Error('Не удалось получить инфоблоки'));
        }

        foreach ($rows as $row) {
            $data[$row['ID']] = $row['NAME'];
        }

        return $result->setData($data);
    }
}