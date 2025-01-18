<?php
namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\DealService;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Services\NotificationService;

Loc::loadMessages(__FILE__);

class DealHandler
{
    use ModuleTrait;
    use HandlerTrait;

    /**
     * Блокирует создание новой сделки ("Заказ наряд")
     * если есть не закрытые, с таким же автомобилем
     *
     * @param $arFields
     * @return false|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function beforeAdd(&$arFields)
    {
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $carService = new CarService;
        $dealService = new DealService;
        $moduleService = ModuleService::getInstance();

        $dealPropCategoryId  = $moduleService->getPropVal('OTUS_AUTOSERVICE_DEAL_CATEGORY');

        $dealCategory = $arFields['CATEGORY_ID'];

        if ($dealPropCategoryId != $dealCategory) {
            return;
        }

        $request = Context::getCurrent()->getRequest();

        $clientData = json_decode($request->getPost('CLIENT_DATA'));

        $contactEmptyErrorMess = Loc::getMessage('OTUS_AUTOSERVICE_DEAL_ADD_ERROR_CONTACT_EMPTY');

        if (empty($clientData) || !is_object($clientData)) {
            $arFields['RESULT_MESSAGE'] = $contactEmptyErrorMess;

            return false;
        }

        if (!is_array($clientData->CONTACT_DATA)) {
            $arFields['RESULT_MESSAGE'] = $contactEmptyErrorMess;

            return false;
        }

        $contactData = current($clientData->CONTACT_DATA);

        if (!property_exists($contactData, 'id')) {
            $arFields['RESULT_MESSAGE'] = Loc::getMessage('OTUS_AUTOSERVICE_DEAL_ADD_ERROR_CONTACT_NEW_NOT_CAR');

            return false;
        }

        $carId = $arFields[$dealService->propCarCode];

        // Проверяем что автомобиль указан
        if (!$carId) {
            $arFields['RESULT_MESSAGE'] = Loc::getMessage('OTUS_AUTOSERVICE_DEAL_ADD_ERROR_CAR_EMPTY');

            return false;
        }

        $carName = $carService->getCarName($carId);

        // Проверяем что автомобиль пренадлежит контакту
        if (!$carService->isCatRelatedContact($carId, $contactData->id)) {
            $arFields['RESULT_MESSAGE'] = Loc::getMessage(
                'OTUS_AUTOSERVICE_DEAL_ADD_ERROR_CAR_NOT_RELATED_CONTACT',
                [
                    '#CAR#' => $carName
                ]
            );

            return false;
        }

        if ($dealId = $dealService->getOpenDealByCar($carId)) {
           $dealName = $dealService->getDealName($dealId);

           $errMessage = Loc::getMessage(
                "OTUS_AUTOSERVICE_NO_CLOSED_DEAL_BY_CAR_NOTIFY",
                [
                    '#DEAL_ID#' => $dealId,
                    '#DEAL_NAME#' => $dealName,
                    '#CAR_NAME#' => $carName
                ]
            );

           (new NotificationService)->sendNotification(
               $arFields['CREATED_BY_ID'],
               $arFields['ASSIGNED_BY_ID'],
               $errMessage
           );

            $arFields['RESULT_MESSAGE'] = strip_tags($errMessage);

           return false;
        }

        self::$handlerDisallow = false;
    }

    /**
     * Хендлер для события OnAfterCrmDealAdd
     * после добавления сделки, переименовывает сделку
     * @param $arFields
     * @return void
     */
    public static function afterAdd($arFields)
    {
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $dealService = new DealService;

        $moduleService = ModuleService::getInstance();
        $dealPropCategoryId  = $moduleService->getPropVal('OTUS_AUTOSERVICE_DEAL_CATEGORY');

        $dealId = $arFields['ID'];

        if ($dealPropCategoryId != $dealService->getDealCategoryId($dealId)) {
            return;
        }

        $arFieldsDeal = [
            'TITLE' => Loc::getMessage('OTUS_AUTOSERVICE_DEAL_NAME', [
                '#DEAL_ID#' => $dealId,
            ]),
        ];

        (new \CCrmDeal)->Update($dealId, $arFieldsDeal, true, true, []);

        self::$handlerDisallow = false;
    }
}
