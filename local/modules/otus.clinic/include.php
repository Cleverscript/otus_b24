<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = "otus.clinic";

$defaultOptions = Option::getDefaults($module_id);

Loader::registerAutoLoadClasses(null, [
    'Otus\Clinic\Models\AbstractIblockPropertyValuesTable' => "/local/modules/{$module_id}/lib/Models/AbstractIblockPropertyValuesTable.php",
    'Otus\Clinic\Models\Lists\DoctorsTable' => "/local/modules/{$module_id}/lib/Models/Lists/DoctorsTable.php",
    'Otus\Clinic\Models\Lists\ProceduresTable' => "/local/modules/{$module_id}/lib/Models/Lists/ProceduresTable.php",
    'Otus\Clinic\Services\DoctorService' => "/local/modules/{$module_id}/lib/Services/DoctorService.php",
    'Otus\Clinic\Services\ProcedureService' => "/local/modules/{$module_id}/lib/Services/ProcedureService.php",
    'Otus\Clinic\Services\IblockHelper' => "/local/modules/{$module_id}/lib/Services/IblockHelper.php",
    'Otus\Clinic\Utils\BaseUtils' => "/local/modules/{$module_id}/lib/Utils/BaseUtils.php",
]);