<?php

namespace Otus\SyncDealIblock\Handlers;

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

        $iblockId = Option::get(self::$moduleId, self::$requireProps['IBLOCK_ID']);
        $sumPropId = Option::get(self::$moduleId, self::$requireProps['SUM']);
        $assignedPropId = Option::get(self::$moduleId, self::$requireProps['ASSIGNED']);

        $diffVals = IblockHelper::diffChangePropsVals(
            $iblockId,
            [$sumPropId, $assignedPropId],
            $arFields
        );

        if (!empty($diffVals)) {
            pLog([__METHOD__ => $diffVals]);
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
