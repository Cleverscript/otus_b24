<?php

namespace Otus\SyncDealIblock\Exceptions;

use Bitrix\Main\Localization\Loc;
use Otus\SyncDealiblock\Traits\ModuleTrait;

class ModuleException
{
    use ModuleTrait;

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