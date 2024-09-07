<?php

namespace Otus\Clinic\Models\Lists;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Otus\Clinic\Models\Lists\ProceduresTable;
use Otus\Clinic\Models\AbstractIblockPropertyValuesTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class DoctorsTable extends AbstractIblockPropertyValuesTable
{
    protected static $iblockEntityId = null;

    public static function query()
    {
        self::$iblockEntityId = (int) Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_DOCTORS');

        if (!intval(self::$iblockEntityId)) {
            throw new \RuntimeException('The ID of the infoblock with Doctors is not defined');
        }

        return parent::query();
    }

    public static function getMap(): array
    {
        $referencePropCode = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_PROP_REFERENCE');

        if (empty($referencePropCode)) {
            throw new \RuntimeException('The property code for linking the Doctors and Procedures infoblocks is not defined');
        }

        $map = [];

        $map['ID'] = (new IntegerField('ID',
            []
        ))->configurePrimary(true)
            ->configureAutocomplete(true);

        $map['NAME'] = (new StringField('NAME',
            []
        ));

        $map['PROCEDURES'] = new ExpressionField(
            'PROCEDURES',
            sprintf('(select group_concat(e.ID, ";", e.NAME SEPARATOR "\0") as VALUE from %s as m join b_iblock_element as e on m.VALUE = e.ID where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
                static::getTableNameMulti(),
                '%s',
                static::getPropertyId($referencePropCode)
            ),
            ['IBLOCK_ELEMENT_ID'],
            ['fetch_data_modification' => [static::class, 'getMultipleFieldIdValueModifier']]
        );


        /*$map['PROCED_NEW'] =
            (new Reference('PROCEDURES_NEW_ID',
                ProceduresTable::class,
                Join::on('this.PROCEDURES_NEW_ID', 'ref.ID')
            ));*/

        return array_merge(parent::getMap(), $map);
    }
}
