<?php

namespace Otus\SyncDealIblock\Handlers;

use Bitrix\Crm\DealTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\SyncDealIblock\Utils\BaseUtils;
use Otus\SyncDealIblock\Helpers\IblockHelper;
use Otus\SyncDealIblock\Exceptions\ModuleException;

class IblockHandlers
{
    protected static $moduleId = 'otus.syncdealiblock';

    protected static $requireProps = [
        'IBLOCK_ID' => 'OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK',
        'DEAL' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_DEAL_CODE',
        'SUM' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_SUM_CODE',
        'ASSIGNED' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_ASSIGNED_CODE',
        'ORDER' => 'OTUS_SYNCDEALIBLOCK_CRM_DEAL_PROP_UF_ORDER'
    ];

    public static function beforeAdd(&$arFields)
    {
        foreach (self::$requireProps as $key => $code) {
            if (Option::get(self::$moduleId, $code) == false) {

                ModuleException::exceptionModuleOption($key, self::$requireProps);

                return false;
            }
        }
    }

    public static function afterAdd(&$arFields)
    {
        $sumPropId = Option::get(self::$moduleId, self::$requireProps['SUM']);

        if (!$sumPropId) {
            return false;
        }

        $dealPropId = Option::get(self::$moduleId, self::$requireProps['DEAL']);

        if (!$dealPropId) {
            return false;
        }

        $assignedPropId = Option::get(self::$moduleId, self::$requireProps['ASSIGNED']);

        if (!$assignedPropId) {
            return false;
        }

        $orderPropDealCode = Option::get(self::$moduleId, self::$requireProps['ORDER']);
        if (empty($orderPropDealCode)) {
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
            $orderPropDealCode => $arFields['ID']
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

            \CIBlockElement::SetPropertyValuesEx(
                $arFields['ID'],
                $arFields['IBLOCK_ID'],
                [$dealPropId => $dealId]
            );

            (new \CIBlockElement)->Update(
                $arFields['ID'],
                [
                    'NAME' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_ORDER_NAME_NEW', [
                        '#DEAL_ID#' => $dealId,
                        '#ORDER_ID#' => $arFields['ID'],
                    ])
                ]
            );
        }
    }

    public static function beforeUpdate(&$arFields)
    {
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

        $dealId = IblockHelper::getDealIdFromOrder($iblockId, $elementId, $dealPropId);

        if (!$dealId->isSuccess()) {
            $APPLICATION->throwException(
                BaseUtils::extractErrorMessage($dealId->getErrorMessages())
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

            (new \CCrmDeal)->Update(
                $dealId,
                $dealUpdFields,
                true,
                true,
                ['DISABLE_USER_FIELD_CHECK' => true]
            );
        }
    }

    public static function beforeDelete($id)
    {
        global $APPLICATION;

        $iblockId = Option::get(self::$moduleId, self::$requireProps['IBLOCK_ID']);
        $dealPropId = Option::get(self::$moduleId, self::$requireProps['DEAL']);

        $dealId = IblockHelper::getDealIdFromOrder($iblockId, $id, $dealPropId);

        if (!$dealId->isSuccess()) {
            $APPLICATION->throwException(
                BaseUtils::extractErrorMessage($dealId->getErrorMessages())
            );

            return false;
        }

        $dealId = current($dealId->getData());

        if (!(new \CCrmDeal)->Exists($dealId)) {
            return true;
        }

        $dealInfo = DealTable::query()
            ->where('ID', $dealId)
            ->setSelect(['CLOSED'])
            ->exec()->fetch();

        if ($dealInfo['CLOSED'] != 'Y') {
            $APPLICATION->throwException(
                Loc::getMessage(
                    'OTUS_SYNCDEALIBLOCK_ORDER_DELETE_DEAL_CLOSED_FAIL',
                    ['#DEAL_ID#' => $dealId, '#ORDER_ID#' => $id]
                )
            );

            return false;
        }
    }

    public static function afterDelete($arFields)
    {
        $orderPropDealCode = Option::get(self::$moduleId, self::$requireProps['ORDER']);
        if (empty($orderPropDealCode)) {
            return false;
        }

        $dealId = DealTable::query()
            ->where($orderPropDealCode, $arFields['ID'])
            ->setSelect(['ID'])
            ->exec()->fetch()['ID'];

        if ($dealId) {
            (new \CCrmDeal)->Delete($dealId);
        }
    }
}
