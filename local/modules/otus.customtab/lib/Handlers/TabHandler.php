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
        $lastTab = end($tabs);

        dump($lastTab);


        if ($entityTypeID === \CCrmOwnerType::Deal) {
            $lastTab['id'] = 'otus_customtab';
            $lastTab['name'] = 'Свой контент';
            $lastTab['html'] = 'Свой контент';
            $tabs[] = $lastTab;
        }

        dump($lastTab);

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}
