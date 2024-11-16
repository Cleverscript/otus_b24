<?php

namespace Otus\SyncDealIblock\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

/**
 * Класс с хелпер-методами для инфоблока
 */
class IblockHelper
{
    /**
     * Возвращает объект с массивом всех инфоблоков тип список в системе
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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

    /**
     * Возвращает объект с массивом всех св-ств инфоблока
     * @param int $iblockId
     * @return Result
     */
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

    /**
     * Возвращает значение св-ва элемента инфоблока по его id
     * @param int $propId
     * @param array $arFields
     * @return false|mixed|string
     */
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

    /**
     * Возвращает массив св-ств инфоблока по их ID
     * @param int $iblockId
     * @param array $propsIds
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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

    /**
     * Возвращает массив с сввами элемента инфоблока
     * в которых изменились значения
     * @param int $iblockId
     * @param array $propsIds
     * @param array $arFields
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function diffChangePropsVals(int $iblockId, array $propsIds, array $arFields): array
    {
        $result = [];
        $elementId = $arFields['ID'];

        $propsCodes = self::getIblockProperties($iblockId, $propsIds);

        $elementObj = Iblock::wakeUp($iblockId)
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

    /**
     * Возвращает объек с ID сделки прикрепленной к эл-ту инфоблока, по его ID
     * @param int $iblockId
     * @param int $elementId
     * @param int $dealPropId
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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

    /**
     * Возвращает объек содержащий ID эл-та инфоблока связанного со сделкой, по ID сделки
     * @param int $iblockId
     * @param int $dealId
     * @param int $dealPropId
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getOrderIdFromDeal(int $iblockId, int $dealId, int $dealPropId): Result
    {
        $result = new Result;

        if (!$iblockId) {
            $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_EMPTY')
            ));
        }

        if (!$dealId) {
            $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_ID_EMPTY')
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

        $dealPropCode = IblockHelper::getIblockProperties($iblockId, [$dealPropId]);

        if (empty($dealPropCode)) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBL_ELEM_CODE_DEAL_IS_EMPTY')
            ));
        }

        $elementObj = Iblock::wakeUp($iblockId)
            ->getEntityDataClass()::query()
            ->where(current($dealPropCode) . '.VALUE', $dealId)
            ->setSelect(['ID'])
            ->fetchObject();

        if (!is_object($elementObj)) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_ELEM_EMPTY')
            ));
        }

        $orderId = $elementObj?->getId();

        if (!$orderId) {
            return $result->addError(new Error(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_ELEM_DEAL_ID_EMPTY', ['#DEAL_ID#' => $dealId])
            ));
        }

        return $result->setData([$orderId]);
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
     * Возвращает истину или ложь является ли инфоблок разрешенным
     * для выполнения хендлер-методов на обработчиках событий
     * используется для прерывания выполнения
     * @param $elementId
     * @param string $moduleId
     * @param int $iblockId
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function isAllowIblock($elementId = null, string $moduleId, int $iblockId = 0): bool
    {
        if (!$iblockId) {
            $iblockId = self::getIblockIdByElement($elementId);
        }

        return $iblockId == Option::get($moduleId, 'OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK');
    }
}