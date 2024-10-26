<?php

namespace Otus\SyncDealIblock\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use \Bitrix\Iblock\PropertyTable;

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

    public static function getElementPropValue(int $propId, array $arFields)
    {
        switch (true) {
            case is_array($arFields['PROPERTY_VALUES'][$propId]):{
                $val = current($arFields['PROPERTY_VALUES'][$propId]);
                $val = (is_array($val))? $val['VALUE'] : $val;

                break;
            }
            default :{
                $val = $arFields['PROPERTY_VALUES'][$propId];

                break;
            }
        }

        return $val;
    }

    public static function diffChangePropsVals(int $iblockId, array $propsIds, array $arFields)
    {
        $elementId = $arFields['ID'];

        $rows = PropertyTable::getList(
            [
                'filter' => ['IBLOCK_ID' => $iblockId],
                'select' => ['*']
            ]
        )->fetchAll();

        $propsCodes = [];
        $propsCodeSelect = [];

        pLog($propsIds, '__diffChangePropsVals.log');

        foreach ($rows as $row) {
            if (!in_array($row['ID'], $propsIds)) continue;

            pLog($row['ID'], '__diffChangePropsVals.log');

            $propsCodes[$row['ID']] = $row['CODE'];
            $propsCodeSelect[] = $row['CODE'];
        }

        pLog($propsCodeSelect, '__diffChangePropsVals.log');

        unset($rows);

        $elementObj = $object = Iblock::wakeUp($iblockId)
            ->getEntityDataClass()::query()
            ->where('ID', $elementId)
            ->setSelect($propsCodeSelect)
            ->fetchObject();

        pLog($elementObj?->get('SUM')?->getValue(), '__diffChangePropsVals.log');
    }
}