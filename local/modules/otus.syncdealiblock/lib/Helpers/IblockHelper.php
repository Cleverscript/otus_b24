<?php

namespace Otus\SyncDealIblock\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use \Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

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

    public static function getIblockProperties(int $iblockId, array $propsIds): array
    {
        $result = [];

        $rows = PropertyTable::getList(
            [
                'filter' => ['IBLOCK_ID' => $iblockId],
                'select' => ['*']
            ]
        )->fetchAll();

        foreach ($rows as $row) {
            if (!in_array($row['ID'], $propsIds)) continue;
            $result[$row['ID']] = $row['CODE'];
        }

        unset($rows);

        return $result;
    }

    public static function diffChangePropsVals(int $iblockId, array $propsIds, array $arFields): array
    {
        $result = [];
        $elementId = $arFields['ID'];

        $propsCodes = self::getIblockProperties($iblockId, $propsIds);

        $elementObj = $object = Iblock::wakeUp($iblockId)
            ->getEntityDataClass()::query()
            ->where('ID', $elementId)
            ->setSelect(array_values($propsCodes))
            ->fetchObject();


        foreach ($propsCodes as $propId => $code) {
            $newVal = self::getElementPropValue($propId, $arFields);

            if ($elementObj?->get($code)?->getValue() === $newVal) continue;

            $result[$propId] = ['CODE' => $code,  'VALUE' => $newVal];
        }

        unset($elementObj);

        return $result;
    }

    public static function getDealIdFromOrder(int $iblockId, int $elementId, int $dealPropId): Result
    {
        $result = new Result;

        if (!$iblockId) {
            $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_EMPTY')
            ));
        }

        if (!$elementId) {
            $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_ORDER_ID_EMPTY')
            ));
        }

        if (!$dealPropId) {
            $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_PROP_ID_EMPTY')
            ));
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        $propsCodes = IblockHelper::getIblockProperties($iblockId, [$dealPropId]);

        if (empty($propsCodes)) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBL_ELEM_CODE_DEAL_IS_EMPTY')
            ));
        }

        pLog([$elementId, $propsCodes]);

        $elementObj = Iblock::wakeUp($iblockId)
            ->getEntityDataClass()::query()
            ->where('ID', $elementId)
            ->setSelect(array_values($propsCodes))
            ->fetchObject();

        if (!is_object($elementObj)) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_ELEM_EMPTY')
            ));
        }

        $dealId = $elementObj?->get(current($propsCodes))?->getValue();

        if (!$dealId) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_ELEM_DEAL_ID_EMPTY', ['#ORDER_ID#' => $elementId])
            ));
        }

        return $result->setData([$dealId]);
    }
}