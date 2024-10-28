<?php

namespace Otus\SyncDealIblock\Handlers;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\SyncDealIblock\Helpers\IblockHelper;
use Otus\SyncDealIblock\Exceptions\ModuleException;

class IblockHandlers
{
    protected static $moduleId = 'otus.syncdealiblock';

    protected static $requireProps = [
        'IBLOCK_ID' => 'OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK',
        'DEAL' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_DEAL_CODE',
        'SUM' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_SUM_CODE',
        'ASSIGNED' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_ASSIGNED_CODE'
    ];

    public static function beforeAdd(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);

        foreach (self::$requireProps as $key => $code) {
            if (Option::get(self::$moduleId, $code) == false) {

                ModuleException::exceptionModuleOption($key, self::$requireProps);

                return false;
            }
        }
    }

    public static function afterAdd(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);

        $sumPropId = Option::get(self::$moduleId, self::$requireProps['SUM']);
        $dealPropId = Option::get(self::$moduleId, self::$requireProps['DEAL']);
        $assignedPropId = Option::get(self::$moduleId, self::$requireProps['ASSIGNED']);

        if (!$sumPropId) {
            return false;
        }

        if (!$dealPropId) {
            return false;
        }

        if (!$assignedPropId) {
            return false;
        }

        $sumDeal = IblockHelper::getElementPropValue($sumPropId, $arFields);

        if (!$sumDeal) {
            return true;
        }

        $assignedDeal = IblockHelper::getElementPropValue($assignedPropId, $arFields);

        if (!$assignedDeal) {
            return true;
        }

        $arFieldsDeal = [
            'TITLE' => $arFields['NAME'],
            'OPPORTUNITY' => $sumDeal,
            'ASSIGNED_BY_ID' => $assignedDeal,
            'STAGE_ID' => 'NEW',
        ];

        $deal = new \CCrmDeal;

        $dealId = $deal->Add($arFieldsDeal, true, []);

        $arFieldsDeal = [
            'TITLE' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_TITLE_NEW', [
                '#DEAL_ID#' => $dealId,
                '#ORDER_ID#' => $arFields['ID'],
            ]),
        ];

        if ($dealId) {
            $deal->Update($dealId, $arFieldsDeal, true, true, []);

            (new \CIBlockElement)->Update(
                $arFields['ID'],
                [
                    'NAME' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_ORDER_NAME_NEW', [
                        '#DEAL_ID#' => $dealId,
                        '#ORDER_ID#' => $arFields['ID'],
                    ])
                ]
            );

            \CIBlockElement::SetPropertyValuesEx(
                $arFields['ID'],
                $arFields['IBLOCK_ID'],
                [$dealPropId => $dealId]
            );
        }
    }

    public static function beforeUpdate(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);

        global $APPLICATION;

        $dealId = null;
        $dealUpdFields = [];
        $elementId = $arFields['ID'];

        $iblockId = Option::get(self::$moduleId, self::$requireProps['IBLOCK_ID']);
        $sumPropId = Option::get(self::$moduleId, self::$requireProps['SUM']);
        $dealPropId = Option::get(self::$moduleId, self::$requireProps['DEAL']);
        $assignedPropId = Option::get(self::$moduleId, self::$requireProps['ASSIGNED']);

        $propsCodes = IblockHelper::getIblockProperties($iblockId, [$dealPropId]);

        if (empty($propsCodes)) {
            $APPLICATION->throwException(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBL_ELEM_CODE_DEAL_IS_EMPTY')
            );

            return false;
        }

        $elementObj = $object = Iblock::wakeUp($iblockId)
            ->getEntityDataClass()::query()
            ->where('ID', $elementId)
            ->setSelect(array_values($propsCodes))
            ->fetchObject();

        if (!is_object($elementObj)) {
            $APPLICATION->throwException(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_IBLOCK_ELEM_EMPTY')
            );

            return false;
        }

        $dealId = $elementObj?->get(current($propsCodes))?->getValue();

        if (!intval($dealId)) {
            $APPLICATION->throwException(
                Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_ID_IS_EMPTY')
            );

            return false;
        }

        $diffVals = IblockHelper::diffChangePropsVals(
            $iblockId,
            [$sumPropId, $assignedPropId],
            $arFields
        );

        if (!empty($diffVals)) {
            foreach ($diffVals as $propId => $prop) {
                switch ($propId) {
                    case $sumPropId: {
                        $dealUpdFields['OPPORTUNITY'] = $prop['VALUE'];
                        break;
                    }
                    case $assignedPropId: {
                        $dealUpdFields['ASSIGNED_BY_ID'] = $prop['VALUE'];
                        break;
                    }
                    default : {
                        break;
                    }
                }
            }
            pLog([__METHOD__ => $diffVals]);

            $res = (new \CCrmDeal)->Update($dealId, $dealUpdFields, true, true, ['DISABLE_USER_FIELD_CHECK' => true]);
        }
    }

    public static function beforeDelete(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);
    }

    public static function afterDelete(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);
    }
}
