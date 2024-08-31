<?php

namespace Otus\Clinic\Models\Lists;

use Bitrix\Main\Config\Option;
use Otus\Clinic\Models\AbstractIblockPropertyValuesTable;

class ProceduresTable extends AbstractIblockPropertyValuesTable
{
    protected static $iblockEntityId = null;

    public static function query()
    {
        self::$iblockEntityId = (int) Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_DOCTORS');

        if (!intval(self::$iblockEntityId)) {
            throw new \RuntimeException('Не определен ID инфоблока с Докторами');
        }

        return parent::query();
    }
}
