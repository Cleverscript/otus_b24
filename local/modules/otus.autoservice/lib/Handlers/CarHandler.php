<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Helpers\IblockHelper;
use Otus\Autoservice\Services\HighloadBlockService;

Loc::loadMessages(__FILE__);

class CarHandler
{
    use HandlerTrait;
    use ModuleTrait;

    public static function beforeAdd(&$arFields)
    {
        dump($arFields);

        if (!IblockHelper::isAllowIblock(null, self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $itemName = [];
        $propertyValues = $arFields['PROPERTY_VALUES'];
        $hlBlockService = new HighloadBlockService;

        $carPropBrandId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_CARS_PROP_BRAND');
        if (!$carPropBrandId) {
            throw new \Exception(Loc::loadMessages('OTUS_AUTOSERVICE_IB_CARS_PROP_BRAND_NULL'));
        }

        $carPropModelId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_CARS_PROP_MODEL');
        if (!$carPropModelId) {
            throw new \Exception(Loc::loadMessages('OTUS_AUTOSERVICE_IB_CARS_PROP_MODEL_NULL'));
        }

        $carPropVinId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_CARS_PROP_VIN');
        if (!$carPropVinId) {
            throw new \Exception(Loc::loadMessages('OTUS_AUTOSERVICE_IB_CARS_PROP_VIN_NULL'));
        }

        if (!empty($propertyValues[$carPropBrandId])) {
            $brand = $hlBlockService->getHLItemByXmlId(
                current($propertyValues[$carPropBrandId])['VALUE'],
                $hlBlockService->getEntityHLBrand()
            )['UF_NAME'];

            $itemName[] = $brand;
        }

        if (!empty($propertyValues[$carPropModelId])) {
            $model = $hlBlockService->getHLItemByXmlId(
                current($propertyValues[$carPropModelId])['VALUE'],
                $hlBlockService->getEntityHLModel()
            )['UF_NAME'];
            $itemName[] = $model;
        }

        if (!empty($propertyValues[$carPropVinId])) {
            $itemName[] = current($propertyValues[$carPropVinId])['VALUE'];
        }

        if (!empty($itemName)) {
            $arFields['NAME'] = implode(' ', $itemName);

            dump($itemName);
        }

        dump($arFields);

        /*(new \CIBlockElement)->Update(
            $itemId,
            [
                'NAME' => "---"
            ]
        );*/

        self::$handlerDisallow = false;

        return $arFields;
    }
}