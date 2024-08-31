<?php

namespace Otus\Clinic\Models\Lists;

use Bitrix\Main\Config\Option;
use Otus\Clinic\Models\AbstractIblockPropertyValuesTable;
use Bitrix\Main\ORM\Fields\ExpressionField;

class DoctorsTable extends AbstractIblockPropertyValuesTable
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

    public static function getMap(): array
    {
        $map['PROCEDURES'] = new ExpressionField(
            'PROCEDURES',
            sprintf('(select group_concat(e.ID, ";", e.NAME SEPARATOR "\0") as VALUE from %s as m join b_iblock_element as e on m.VALUE = e.ID where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
                static::getTableNameMulti(),
                '%s',
                static::getPropertyId('PROCEDURES_ID')
            ),
            ['IBLOCK_ELEMENT_ID'],
            ['fetch_data_modification' => [static::class, 'getMultipleFieldIdValueModifier']]
        );

        return parent::getMap() + $map;
    }
}
