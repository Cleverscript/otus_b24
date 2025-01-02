<?php

namespace Otus\Autoservice\Handlers;

use CIMNotify;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\DealService;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class  DealHandler
{
    use ModuleTrait;
    use HandlerTrait;

    public static function beforeAdd(&$arFields)
    {
        $dealService = new DealService;

        if ($carId = $arFields[$dealService->propCarCode]) {

           $carService = new CarService;
           $carName = $carService->getCarName($carId);

           if ($dealId = $dealService->getOpenDealByCar($carId)) {
               $creatorId = $arFields['CREATED_BY_ID'];
               $assignedId = $arFields['ASSIGNED_BY_ID'];

               $dealName = $dealService->getDealName($dealId);

               if (Loader::includeModule("im")) {
                   $fields = [
                       "FROM_USER_ID" => $creatorId,
                       "TO_USER_ID" => $assignedId,
                       "NOTIFY_TYPE" => 4,
                       "NOTIFY_MODULE" => self::$moduleId,
                       "NOTIFY_TAG" => "",
                       "NOTIFY_MESSAGE" => Loc::getMessage("OTUS_AUTOSERVICE_NOTIFY", ['#DEAL_ID#' => $dealId, '#DEAL_NAME#' => $dealName, '#CAR_NAME#' => $carName]),
                   ];

                   CIMNotify::Add($fields);
               }
           }
        }
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