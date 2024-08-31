<?php

namespace Otus\Clinic\Utils;

use Bitrix\Main\Result;

class BaseUtils
{
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

    public static function extractErrorMessage(Result $result): string
    {
        return implode('; ', $result->getErrorMessages());
    }

    public static function getFieldKeyByEntityClass(string $class, string $fieldKey): string
    {
        if (empty($class)) {
            throw new \Exception('Class name is empty');
        }

        $key = mb_strtoupper(str_replace(['Table', '\\'], ['', '_'], $class));
        $key .= '_' .str_replace('.', '_', $fieldKey);

        return $key;
    }

    public static function getFieldNameElement(string $name): string
    {
        if (empty($name)) {
            throw new \Exception('Field name is empty');
        }

        return str_replace('ELEMENT.', '', $name);
    }
}