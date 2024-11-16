<?php

namespace Otus\SyncDealIblock\Handlers;

use Bitrix\Crm\DealTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\SyncDealIblock\Utils\BaseUtils;
use Otus\SyncDealIblock\Traits\ModuleTrait;
use Otus\SyncDealIblock\Traits\HandlerTrait;
use Otus\SyncDealIblock\Helpers\IblockHelper;
use Otus\SyncDealIblock\Contracts\Handlers\BaseHandler;
use Otus\SyncDealIblock\Exceptions\ModuleException;

/**
 * Класс содержащий хендлер-методы
 * для обработчиков элементов инфоблока
 * - OnBeforeIBlockElementAdd
 * - OnAfterIBlockElementAdd
 * - OnBeforeIBlockElementUpdate
 * - OnBeforeIBlockElementDelete
 * - OnAfterIBlockElementDelete
 */
class IblockHandler implements BaseHandler
{
    use ModuleTrait;
    use HandlerTrait;

    /**
     * Хендлер для события OnBeforeIBlockElementAdd
     * проверяет все ли обязательные опции модуля установлены
     * и если нет, то выбрасывает исключение
     * @param $arFields
     * @return false|void
     */
    public static function beforeAdd(&$arFields)
    {
        if (!IblockHelper::isAllowIblock($arFields['ID'], self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        foreach (self::REQUIRE_PROPS as $key => $code) {
            if (Option::get(self::$moduleId, $code) == false) {

                ModuleException::exceptionModuleOption($key, self::REQUIRE_PROPS);

                return false;
            }
        }
    }

    /**
     * Хендлер для события OnAfterIBlockElementAdd
     * после добавления элемента инфоблока, добавляет сделку
     * и связывает ее с элементом по его ID. После добавления сделки,
     * обновляет ее название и название элемента инфоблока, указывая в них ID связанных сущностей
     * @param $arFields
     * @return bool|void
     */
    public static function afterAdd($arFields)
    {
        if (!IblockHelper::isAllowIblock($arFields['ID'], self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $sumPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['SUM']);

        if (!$sumPropId) {
            return false;
        }

        $iblockId = $arFields['IBLOCK_ID'];
        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);
        $dealPropCode = current(IblockHelper::getIblockProperties($iblockId, [$dealPropId]));

        if (!$dealPropId) {
            return false;
        }

        $deal = new \CCrmDeal;

        $orderId = $arFields['ID'];
        $dealId = $arFields['PROPERTY_VALUES'][$dealPropCode];

        if (!$dealId) {
            $assignedPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['ASSIGNED']);

            if (!$assignedPropId) {
                return false;
            }

            $orderPropDealCode = Option::get(self::$moduleId, self::REQUIRE_PROPS['ORDER']);
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

            $dealId = $deal->Add($arFieldsDeal, true, []);
        }

        $arFieldsDeal = [
            'TITLE' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_DEAL_TITLE_NEW', [
                '#DEAL_ID#' => $dealId,
                '#ORDER_ID#' => $orderId,
            ]),
        ];

        if ($dealId) {
            $deal->Update($dealId, $arFieldsDeal, true, true, []);

            \CIBlockElement::SetPropertyValuesEx(
                $orderId,
                $arFields['IBLOCK_ID'],
                [$dealPropId => $dealId]
            );

            (new \CIBlockElement)->Update(
                $orderId,
                [
                    'NAME' => Loc::getMessage('OTUS_SYNCDEALIBLOCK_ORDER_NAME_NEW', [
                        '#DEAL_ID#' => $dealId,
                        '#ORDER_ID#' => $orderId,
                    ])
                ]
            );
        }

        self::$handlerDisallow = false;
    }

    /**
     * Хендлер для события OnBeforeIBlockElementUpdate
     * проверяет есть ли изменения в св-вах эл-та инфоблока,
     * до его сохранения при редактировании, и если они есть,
     * то обновляет эти же значения в сделке (стоимость, ответсвенный)
     * @param $arFields
     * @return false|void
     */
    public static function beforeUpdate(&$arFields)
    {
        if (!IblockHelper::isAllowIblock($arFields['ID'], self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $dealId = null;
        $dealUpdFields = [];
        $elementId = $arFields['ID'];

        $iblockId = Option::get(self::$moduleId, self::REQUIRE_PROPS['IBLOCK_ID']);
        $sumPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['SUM']);
        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);
        $assignedPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['ASSIGNED']);

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
                BaseUtils::extractErrorMessage($dealId)
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
                current($dealId->getData()),
                $dealUpdFields,
                true,
                true,
                ['DISABLE_USER_FIELD_CHECK' => true]
            );
        }

        self::$handlerDisallow = false;
    }

    /**
     * Хендлер для события OnBeforeIBlockElementDelete
     * перед удалением эл-та инфоблока, проверяется закрыта ли связанная с ним сделка
     * и если нет то выбрасывается исключение
     * @param $id
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function beforeDelete($id)
    {
        if (!IblockHelper::isAllowIblock($id, self::$moduleId)) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $iblockId = Option::get(self::$moduleId, self::REQUIRE_PROPS['IBLOCK_ID']);

        $dealPropId = Option::get(self::$moduleId, self::REQUIRE_PROPS['DEAL']);

        $dealId = IblockHelper::getDealIdFromOrder($iblockId, $id, $dealPropId);

        if ($dealId->isSuccess()) {
            $APPLICATION->throwException(
                BaseUtils::extractErrorMessage($dealId)
            );

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

        self::$handlerDisallow = false;
    }

    /**
     * Хендлер для события OnAfterIBlockElementDelete
     * после удаления эл-та инфоблока, удаляет связанную с ним сделку
     * @param $arFields
     * @return false|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function afterDelete($arFields)
    {
        if (!IblockHelper::isAllowIblock($arFields['ID'], self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $orderPropDealCode = Option::get(self::$moduleId, self::REQUIRE_PROPS['ORDER']);
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

        self::$handlerDisallow = false;
    }
}
