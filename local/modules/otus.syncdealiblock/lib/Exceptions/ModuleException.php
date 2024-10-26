<?php

namespace Otus\SyncDealIblock\Exceptions;

use Bitrix\Main\Localization\Loc;

class ModuleException
{
    protected static $moduleId = 'otus.syncdealiblock';

    public static function exceptionModuleOption(string $code, array $requireProps): void
    {
        global $APPLICATION;

        $APPLICATION->throwException(
            Loc::getMessage($requireProps[$code] . "_EMPTY",
                ['#MODULE_ID#' => self::$moduleId]
            )
        );
    }
}