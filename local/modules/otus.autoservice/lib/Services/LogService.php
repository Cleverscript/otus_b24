<?php
namespace Otus\Autoservice\Services;

use Otus\Autoservice\Traits\ModuleTrait;

class LogService
{
    use ModuleTrait;

    /**
     * Добавляет запись в журнал событий
     * @param int|null $itemId
     * @param string $description
     * @param $auditTypeId
     * @param string $severity
     * @return void
     */
    public static function writeSysLog(?int $itemId, string $description, $auditTypeId = "OTUS_AUTOSERVICE", string $severity = 'DEBUG'): void
    {
        \CEventLog::Add([
            "SEVERITY" => $severity,
            "AUDIT_TYPE_ID" => $auditTypeId,
            "MODULE_ID" => self::$moduleId,
            "ITEM_ID" => $itemId,
            "DESCRIPTION" => $description,
        ]);
    }
}
