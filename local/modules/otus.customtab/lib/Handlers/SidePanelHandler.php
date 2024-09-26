<?php

namespace Otus\Customtab\Helpers;

use Bitrix\Main\Context;
use Bitrix\Main\UI\Extension;

class SidePanelHandler
{
    public static function handleSidepanelLinks()
    {
        $request = Context::getCurrent()->getRequest();

        if ($request->isAjaxRequest()) {
            return true;
        }

        Extension::load([
            "otus.customtab_sidepanel_handler"
        ]);
    }
}