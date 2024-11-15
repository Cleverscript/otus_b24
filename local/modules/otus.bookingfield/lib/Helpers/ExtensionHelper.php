<?php

namespace Otus\Bookingfield\Helpers;

use Otus\Bookingfield\Traits\ModuleTrait;

class ExtensionHelper
{
    use ModuleTrait;

    public static function getExtensions()
    {
        //\Bitrix\Main\UI\Extension::load('otus.bookingfield');

        \CJSCore::RegisterExt(self::$moduleId, [
            'js' => '/local/js/otus/bookingfield/src/booking-procedure.js',
            'css' => '/local/js/otus/bookingfield/src/booking-procedure.css',
            'lang' => '/local/js/otus/bookingfield/lang/' . LANGUAGE_ID . '/message.php',
            'rel' => ['calendar', 'popup']
        ]);

        \CJSCore::init(self::$moduleId);
    }
}