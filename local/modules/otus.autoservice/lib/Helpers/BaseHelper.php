<?php

namespace Otus\Autoservice\Helpers;

use Bitrix\Main\Result;

class BaseHelper
{
    /**
     * Формирование елиной строки со всеми ошибками из объекта тип Result
     *
     * @param Result $result
     * @return string
     */
    public static function extractErrorMessage(Result $result): string
    {
        return implode('; ', $result->getErrorMessages());
    }
}
