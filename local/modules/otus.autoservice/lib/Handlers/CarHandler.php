<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Helpers\IblockHelper;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\HighloadBlockService;

Loc::loadMessages(__FILE__);

class CarHandler
{
    use HandlerTrait;
    use ModuleTrait;

    /**
     * Хендлер метод для события OnStartIBlockElementAdd
     * в котором подменяется NAME эл-та (автомобиля) введенное
     * пользователем в форме создания, на сформированный из значений
     * указанных в св-вах "Марка", "Модель" и "VIN"
     * @param $arFields
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function onStartAdd(&$arFields)
    {
        if (!IblockHelper::isAllowIblock(null, self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $itemName = [];
        $propertyValues = $arFields['PROPERTY_VALUES'];

        $hlBlockService = new HighloadBlockService;
        $moduleService = ModuleService::getInstance();

        $carPropBrandId = $moduleService->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_BRAND');
        $carPropModelId = $moduleService->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_MODEL');
        $carPropVinId = $moduleService->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_VIN');

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
        }

        self::$handlerDisallow = false;
    }

    public static function beforeAdd(&$arFields)
    {
        if (!IblockHelper::isAllowIblock(null, self::$moduleId, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $propertyValues = $arFields['PROPERTY_VALUES'];

        $carPropVinId = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_VIN');

        if (!empty($propertyValues[$carPropVinId])) {
           $vin = current($propertyValues[$carPropVinId])['VALUE'];

           if ((new CarService)->isExists($vin)) {
               global $APPLICATION;

               $APPLICATION->ThrowException(
                   Loc::getMessage('OTUS_AUTOSERVICE_VIN_CODE_IS_EXISTS',
                       ['#VIN#' => $vin]
                   )
               );

               return false;
           }
        }

        self::$handlerDisallow = false;
    }
}