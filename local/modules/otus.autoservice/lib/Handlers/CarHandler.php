<?php
namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Traits\IblockHandlerTrait;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\ContactService;
use Otus\Autoservice\Services\HighloadBlockService;

Loc::loadMessages(__FILE__);

class CarHandler
{
    use HandlerTrait;
    use ModuleTrait;
    use IblockHandlerTrait;

    protected static int $entityIblockId;

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
        self::$entityIblockId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_CARS');

        if (!self::isAllowIblock(null, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $itemName = [];
        $propertyValues = $arFields['PROPERTY_VALUES'];

        $hlBlockService = new HighloadBlockService;
        $moduleService = ModuleService::getInstance();
        $contactService = new ContactService;

        $carPropContactId = $moduleService->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_CONTACT');
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
            $itemName[] = strtoupper(current($propertyValues[$carPropVinId])['VALUE']);
        }

        if (!empty($propertyValues[$carPropContactId])) {
            $contactId = (int) current($propertyValues[$carPropContactId])['VALUE'];
            if ($contactName = trim($contactService->getFullName($contactId))) {
                $itemName[] = "[{$contactName}]";
            }
        }

        if (!empty($itemName)) {
            $arFields['NAME'] = implode(' ', $itemName);
        }

        self::$handlerDisallow = false;
    }

    /**
     * Проверяет VIN код автомобиля на корректность
     * и на уникальность по автомобилям
     * @param $arFields
     * @return false|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function beforeAdd(&$arFields)
    {
        self::$entityIblockId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_CARS');

        if (!self::isAllowIblock(null, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        global $APPLICATION;

        $propertyValues = $arFields['PROPERTY_VALUES'];

        $carService = new CarService;

        $carPropVinId = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_IB_CARS_PROP_VIN');

        if (!empty($propertyValues[$carPropVinId])) {
           $vin = strtoupper(current($propertyValues[$carPropVinId])['VALUE']);

            if (!$carService->isValidVin($vin)) {
                $APPLICATION->ThrowException(
                    Loc::getMessage('OTUS_AUTOSERVICE_VIN_CODE_NOT_VALID',
                        ['#VIN#' => $vin]
                    )
                );

                return false;
            }

           if ($carService->isExists($vin)) {
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