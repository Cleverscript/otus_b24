<?php

namespace Otus\SyncDealIblock\Exceptions;

use Bitrix\Main\Localization\Loc;
use Otus\SyncDealiblock\Traits\ModuleTrait;

/**
 * Класс исключений которые могут возниктнуть в модуле
 */
class ModuleException
{
    use ModuleTrait;

    /**
     * Метод выброса исключений при не настроенных опциях модуля
     * @param string $code
     * @param array $requireProps
     * @return void
     */
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