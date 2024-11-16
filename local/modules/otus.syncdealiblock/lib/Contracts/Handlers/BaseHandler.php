<?php

namespace Otus\SyncDealIblock\Contracts\Handlers;

/**
 * Контракт для классов с хендлер-методами обработчиков событий
 * для элеменов инфоблоков и сделок
 */
interface BaseHandler
{
    const REQUIRE_PROPS = [
        'IBLOCK_ID' => 'OTUS_SYNCDEALIBLOCK_ORDER_IBLOCK',
        'DEAL' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_DEAL_CODE',
        'SUM' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_SUM_CODE',
        'ASSIGNED' => 'OTUS_SYNCDEALIBLOCK_IBLOCK_PROP_ASSIGNED_CODE',
        'ORDER' => 'OTUS_SYNCDEALIBLOCK_CRM_DEAL_PROP_UF_ORDER'
    ];

    public static function beforeAdd(&$arFields);

    public static function afterAdd($arFields);

    public static function beforeUpdate(&$arFields);

    public static function beforeDelete($id);

    public static function afterDelete($arFields);
}