<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = "otus.bookingfield";

$defaultOptions = Option::getDefaults($module_id);

Loader::registerAutoLoadClasses(null, [
    'Otus\Bookingfield\UserTypes\BookingProcedureLink' => "/local/modules/{$module_id}/lib/UserTypes/BookingProcedureLink.php",
]);