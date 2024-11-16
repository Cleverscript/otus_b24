<?php

namespace Otus\SyncDealIblock\Utils;

use Bitrix\Main\Result;

class BaseUtils
{
    /**
     * Метод типизированной проверки на пустое значение
     * для разных типов переменных
     * @param $var
     * @return bool
     */
    public static function isEmpty($var): bool
    {
        if (!isset($var)) {
            return true;
        }
        if (is_array($var)) {
            return empty($var);
        }
        if (is_numeric($var) || is_string($var)) {
            return (int)(function_exists('mb_strlen') ? mb_strlen($var) : strlen($var)) === 0;
        }

        return empty($var);
    }

    /**
     * Метод превращает массив ошибок в строку
     * @param Result $result
     * @return string
     */
    public static function extractErrorMessage(Result $result): string
    {
        return implode('; ', $result->getErrorMessages());
    }
}