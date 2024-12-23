<?php

namespace Otus\Autoservice\Helpers;

class BaseHelper
{
    public static function extractErrorMessage(Result $result): string
    {
        return implode('; ', $result->getErrorMessages());
    }
}