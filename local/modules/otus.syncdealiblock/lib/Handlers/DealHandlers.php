<?php

namespace Otus\SyncDealIblock\Handlers;

use Bitrix\Main\Localization\Loc;

class DealHandlers
{
    public static function afterAdd(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);
    }

    public static function beforeUpdate(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);
    }

    public static function beforeDelete(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);

        // STAGE_ID
        // CLOSED
        // ASSIGNED_BY_ID
        // CREATED_BY_ID
        // MODIFY_BY_ID
        // ADDITIONAL_INFO
    }

    public static function afterDelete(&$arFields)
    {
        pLog([__METHOD__ => $arFields]);
    }
}
