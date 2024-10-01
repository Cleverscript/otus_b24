<?php
namespace Otus\Customtab\Handlers;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class TabHandler
{
    public static function updateTabs(Event $event): EventResult
    {
        $entityTypeID = $event->getParameter('entityTypeID');
        $tabs = $event->getParameter('tabs');

        if ($entityTypeID === \CCrmOwnerType::Deal) {

            /*$component = new \Bitrix\Main\Engine\Response\Component(
                'otus:otus.customtab_grid',
                '',
                ['SET_PAGE_TITLE' => 'N']
            );*/

            global $APPLICATION;

            ob_start();
            $APPLICATION->IncludeComponent(
                "otus:otus.customtab_grid",
                '',
                [
                    "SET_PAGE_TITLE" => "N",
                    "COMPONENT_TEMPLATE" => ".default",
                    "SHOW_ROW_CHECKBOXES" => "Y",
                    "NUM_PAGE" => "5",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "86400"
                ]
            );
            $html = ob_get_contents();
            ob_end_clean();

            $tabs[] = [
                'id' => 'otus_customtab',
                'name' => 'Свой контент',
                'html' => $html
            ];
        }

        //dump($tabs);

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}
