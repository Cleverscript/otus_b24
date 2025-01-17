<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Services\CarService;

Loader::includeModule('crm');

Loc::loadMessages(__FILE__);

class TabHandler
{
    /**
     * Метод встраивания кастомного таба
     * в базовую сущость "Контакт"
     *
     * @param Event $event
     * @return EventResult
     */
    public static function addTabs(Event $event): EventResult
    {
        $entityId = $event->getParameter('entityID');
        $entityTypeID = $event->getParameter('entityTypeID');
        $tabs = $event->getParameter('tabs');

        $canUpdateDeal = \CCrmDeal::CheckUpdatePermission(
            $entityId,
            \CCrmPerms::GetCurrentUserPermissions()
        );

        if ($canUpdateDeal) {

            switch ($entityTypeID) {
                case \CCrmOwnerType::Contact: {
                    $tabs = self::addContactTabs($entityId, $tabs);
                    break;
                }
                case \CCrmOwnerType::Deal: {
                    $tabs = self::addDealTabs($entityId, $tabs);
                    break;
                }
                case \CCrmOwnerType::Lead: {
                    $tabs = self::addLeadTabs($entityId, $tabs);
                    break;
                }
                case \CCrmOwnerType::Company: {
                    $tabs = self::addCompanyTabs($entityId, $tabs);
                    break;
                }
                default: {
                    $tabs = $tabs;
                    break;
                }
            }
        }

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }

    /**
     * Метод формирования параметров кастомного таба
     * для сущности "Контакт"
     *
     * @param $entityId
     * @param $tabs
     * @return array
     */
    private static function addContactTabs($entityId, $tabs): array
    {
        $tabName = null;
        $carService = new CarService;
        $carIblockId = $carService->getCarIblockId();

        foreach ($tabs as $k => $tab) {
            if ($tab['id'] == "tab_lists_{$carIblockId}") {
                $tabName = $tab['name'];
                unset($tabs[$k]);
            }
        }

        $tabs[] = [
            'id' => 'otus_carstab_contact',
            'name' => $tabName,
            'enabled' => true,
            'loader' => [
                'serviceUrl' => '/local/components/otus/autoservice.cars_grid/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        "SET_PAGE_TITLE" => "N",
                        "COMPONENT_TEMPLATE" => ".default",
                        "SHOW_ROW_CHECKBOXES" => "N",
                        "NUM_PAGE" => "5",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "86400",
                        "ENTITY_ID" => $entityId
                    ]
                ]
            ]
        ];

        return $tabs;
    }

    private static function addLeadTabs($entityId, $tabs): array
    {
        return $tabs;
    }

    private static function addCompanyTabs($entityId, $tabs): array
    {
        return $tabs;
    }

    private static function addDealTabs($entityId, $tabs): array
    {
        return $tabs;
    }
}
