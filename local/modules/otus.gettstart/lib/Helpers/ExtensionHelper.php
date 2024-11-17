<?php

namespace Otus\Gettstart\Helpers;

use Otus\Gettstart\Traits\ModuleTrait;

class ExtensionHelper
{
    use ModuleTrait;

    public static function getExtensions()
    {
        \CJSCore::RegisterExt(self::$moduleId, [
            'js' => '/local/js/otus/' . self::$moduleName . '/src/' . self::$moduleName . '.js',
            'css' => '/local/js/otus/' . self::$moduleName . '/src/' . self::$moduleName . '.css',
            'lang' => '/local/js/otus/' . self::$moduleName . '/lang/' . LANGUAGE_ID . '/message.php',
            'rel' => ['popup']
        ]);

        \CJSCore::init(self::$moduleId);
    }
}