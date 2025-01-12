<?php
namespace Otus\Autoservice\Handlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Otus\Autoservice\Services\UserService;
use Otus\Autoservice\Traits\HandlerTrait;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Traits\IblockHandlerTrait;

Loc::loadMessages(__FILE__);

class RequestHandler
{
    use HandlerTrait;
    use ModuleTrait;
    use IblockHandlerTrait;

    protected static int $entityIblockId;

    public static function onStartAdd(&$arFields)
    {
        self::$entityIblockId = Option::get(self::$moduleId, 'OTUS_AUTOSERVICE_IB_REQUESTS');

        if (!self::isAllowIblock(null, $arFields['IBLOCK_ID'])) {
            return;
        }

        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;

        $userName = CurrentUser::get()->getId() ? UserService::getFullName(CurrentUser::get()->getId()) : Loc::getMessage('OTUS_AUTOSERVICE_REQUEST_AUTO_NAME');

        $arFields['NAME'] =  Loc::getMessage('OTUS_AUTOSERVICE_REQUEST_NAME_DEFAULT',
            ['#USER_NAME#' => $userName]
        );

        self::$handlerDisallow = false;
    }
}