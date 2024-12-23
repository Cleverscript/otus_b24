<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('crm');

class TabHandler
{
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
            $tabs = match (true) {
                $entityTypeID === \CCrmOwnerType::Contact => self::addContactTabs($entityId, $entityTypeID, $tabs),
                $entityTypeID === \CCrmOwnerType::Deal => self::addDealTabs($entityId, $entityTypeID, $tabs),
                $entityTypeID === \CCrmOwnerType::Lead => self::addLeadTabs($entityId, $entityTypeID, $tabs),
                $entityTypeID === \CCrmOwnerType::Company => self::addCompanyTabs($entityId, $entityTypeID, $tabs)
            };
        }

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }

    private static function addContactTabs($entityId, $entityTypeID, $tabs): array
    {
        $tabName = null;
        $carIblockId = Option::get('otus.autoservice', "OTUS_AUTOSERVICE_IB_CARS");

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
                        "SHOW_ROW_CHECKBOXES" => "Y",
                        "NUM_PAGE" => "5",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "86400",
                        "ENTITY_ID" => $entityId,
                        "ENTITY_TYPEID" => $entityTypeID
                    ]
                ]
            ]
        ];

        return $tabs;
    }

    private static function addLeadTabs($entityId, $entityTypeID, $tabs): array
    {
        return [];
    }

    private static function addCompanyTabs($entityId, $entityTypeID, $tabs): array
    {
        return [];
    }

    private static function addDealTabs($entityId, $entityTypeID, $tabs): array
    {
        return [];
    }
}
