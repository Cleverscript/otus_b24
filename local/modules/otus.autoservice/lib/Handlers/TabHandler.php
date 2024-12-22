<?php

namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

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
            $tabs = array_merge(
                $tabs,
                match (true) {
                    $entityTypeID === \CCrmOwnerType::Contact => self::addContactTabs(),
                    $entityTypeID === \CCrmOwnerType::Deal => self::addDealTabs(),
                    $entityTypeID === \CCrmOwnerType::Lead => self::addLeadTabs(),
                    $entityTypeID === \CCrmOwnerType::Company => self::addCompanyTabs(),
                }
            );
        }

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }

    private static function addContactTabs(): array
    {
        $tabs[] = [
            'id' => 'otus_carstab_contact',
            'name' => 'Заказы',
            'enabled' => true,
            'loader' => [
                'serviceUrl' => '/local/components/otus/otus.autoservice_cars_grid/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        "SET_PAGE_TITLE" => "N",
                        "COMPONENT_TEMPLATE" => ".default",
                        "SHOW_ROW_CHECKBOXES" => "Y",
                        "NUM_PAGE" => "5",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "86400"
                    ]
                ]
            ]
        ];

        return $tabs;
    }

    private static function addLeadTabs(): array
    {
        return [];
    }

    private static function addCompanyTabs(): array
    {
        return [];
    }

    private static function addDealTabs(): array
    {
        return [];
    }
}
