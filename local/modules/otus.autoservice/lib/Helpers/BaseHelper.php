<?php

namespace Otus\Autoservice\Helpers;

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
