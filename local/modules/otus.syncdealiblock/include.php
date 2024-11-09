<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

$module_id = "otus.syncdealiblock";

$defaultOptions = Option::getDefaults($module_id);

Loader::registerAutoLoadClasses(null, [
    //'Otus\SyncDealiblock\Traits\ModuleTrait' => "/local/modules/{$module_id}/lib/Traits/ModuleTrait.php",
    //'Otus\SyncDealiblock\Traits\HandlerTrait' => "/local/modules/{$module_id}/lib/Traits/HandlerTrait.php",
]);