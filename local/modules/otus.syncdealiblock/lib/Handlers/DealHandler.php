<?php

namespace Otus\SyncDealIblock\Handlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\SyncDealIblock\Traits\ModuleTrait;
use Otus\SyncDealIblock\Traits\HandlerTrait;
use Otus\SyncDealIblock\Helpers\DealHelper;
use Otus\SyncDealIblock\Helpers\IblockHelper;
use Otus\SyncDealIblock\Exceptions\ModuleException;
use Otus\SyncDealIblock\Contracts\Handlers\BaseHandler;
use Otus\SyncDealIblock\Utils\BaseUtils;

class DealHandler implements BaseHandler
{
    use ModuleTrait;
    use HandlerTrait;

    public static function beforeAdd(&$arFields)
    {
        foreach (self::REQUIRE_PROPS as $key => $code) {
            if (Option::get(self::$moduleId, $code) == false) {

                ModuleException::exceptionModuleOption($key, self::REQUIRE_PROPS);

                return false;
            }
        }
    }

    public static function afterAdd($arFields)
    {
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $iblockId = Option::get(self::$moduleId, self::REQUIRE_PROPS['IBLOCK_ID']);
        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);
        $assignedPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['ASSIGNED']);
        $sumPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['SUM']);
        $dealPropCode = current(IblockHelper::getIblockProperties($iblockId, [$dealPropId]));
        $assignedPropCode = current(IblockHelper::getIblockProperties($iblockId, [$assignedPropId]));
        $sumPropCode = current(IblockHelper::getIblockProperties($iblockId, [$sumPropId]));
        $orderPropCode = Option::get(self::$moduleId, self::REQUIRE_PROPS['ORDER']);

        $dealId = $arFields['ID'];
        $orderId = $arFields[$orderPropCode];

        $el = new \CIBlockElement;

        if (!$orderId) {
            $orderProps = [
                $dealPropCode => $arFields['ID'],
                $assignedPropCode => $arFields['ASSIGNED_BY_ID'],
                $sumPropCode => $arFields['OPPORTUNITY_ACCOUNT'],
            ];

            $orderId = $el->Add([
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => $iblockId,
                "PROPERTY_VALUES" => $orderProps,
                "NAME" => $arFields['TITLE'],
                "ACTIVE" => "Y"
            ]);

            if (!$orderId) {
                $APPLICATION->ThrowException($el->LAST_ERROR);
            }
        }

        $el->Update(
            $orderId,
            [
                'NAME' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_ORDER_NAME_NEW', [
                    '#DEAL_ID#' => $dealId,
                    '#ORDER_ID#' => $orderId,
                ])
            ]
        );

        $arFieldsDeal = [
            'TITLE' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_TITLE_NEW', [
                '#DEAL_ID#' => $dealId,
                '#ORDER_ID#' => $orderId,
            ]),
        ];

        (new \CCrmDeal)->Update($dealId, $arFieldsDeal, true, true, []);

        self::$handlerDisallow = false;
    }

    public static function beforeUpdate(&$arFields)
    {
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $iblockId = Option::get(self::$moduleId, self::REQUIRE_PROPS['IBLOCK_ID']);
        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);
        $sumPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['SUM']);
        $assignedPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['ASSIGNED']);
        $sumPropCode = current(IblockHelper::getIblockProperties($iblockId, [$sumPropId]));
        $assignedPropCode = current(IblockHelper::getIblockProperties($iblockId, [$assignedPropId]));

        $orderUpdProps = [];
        $dealId = $arFields['ID'];

        $diffVals = DealHelper::diffChangePropsVals(
            ['ASSIGNED_BY_ID', 'OPPORTUNITY'],
            $arFields
        );

        if (!empty($diffVals)) {
            foreach ($diffVals as $fieldCode => $val) {
                switch ($fieldCode) {
                    case 'OPPORTUNITY': {
                        $orderUpdProps[$sumPropCode] = $val;
                        break;
                    }
                    case 'ASSIGNED_BY_ID': {
                        $orderUpdProps[$assignedPropCode] = $val;
                        break;
                    }
                    default : {
                        break;
                    }
                }
            }
        }

        $orderId = IblockHelper::getOrderIdFromDeal($iblockId, $dealId, $dealPropId);

        if (!$orderId->isSuccess()) {
            $APPLICATION->throwException(
                BaseUtils::extractErrorMessage($orderId)
            );

            return false;
        }

        if (!empty($orderUpdProps)) {
            \CIBlockElement::SetPropertyValuesEx(
                current($orderId->getData()),
                $iblockId,
                $orderUpdProps
            );
        }

        self::$handlerDisallow = false;
    }

    public static function beforeDelete($id)
    {
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $iblockId = Option::get(self::$moduleId, self::REQUIRE_PROPS['IBLOCK_ID']);
        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);

        $arFieldsDeal = ['CLOSED' => 'Y'];

        (new \CCrmDeal)->Update($id, $arFieldsDeal, true, true, []);

        $orderId = IblockHelper::getOrderIdFromDeal($iblockId, $id, $dealPropId);

        if ($orderId->isSuccess()) {
            $APPLICATION->throwException(
                BaseUtils::extractErrorMessage($orderId)
            );

            (new \CIBlockElement)->Delete(current($orderId->getData()));
        }

        self::$handlerDisallow = false;
    }

    public static function afterDelete($arFields)
    {
        return;
    }
}
