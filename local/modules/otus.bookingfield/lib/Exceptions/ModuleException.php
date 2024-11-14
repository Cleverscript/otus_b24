<?php

namespace Otus\Bookingfield\Exceptions;

use Bitrix\Main\Localization\Loc;
use Otus\Bookingfield\Traits\ModuleTrait;

class ModuleException
{
    use ModuleTrait;

    public static function exceptionModuleOption(string $code, array $requireProps): string
    {
        return Loc::getMessage($requireProps[$code] . "_EMPTY",
            ['#MODULE_ID#' => self::$moduleId]
        );
    }
}