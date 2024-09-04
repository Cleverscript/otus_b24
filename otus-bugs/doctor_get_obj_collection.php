<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Otus\Clinic\Models\Lists\DoctorsTable;
use Otus\Clinic\Models\Lists\ProceduresTable;

Loader::includeModule('otus.clinic');


echo '<pre>';
var_dump(array_keys(DoctorsTable::getEntity()->getFields()));
echo '<pre>';

echo '<pre>';
//var_dump(DoctorsTable::getEntity()->getField('PROCEDURES_NEW_ID'));
echo '<pre>';
    //->getRefEntity()->addField(new Reference())

/*
$q = DoctorsTable::query()
    ->setSelect([
        'PROCEDURES',
        //'PROCEDURES_ID_VALUE' => 'PROCEDURES_ID.VALUE',
        'ELEMENT.ID',
        'ELEMENT.NAME',
        'PROCEDURES_NEW_ID' //=> 'PROCEDURES_NEW_ID.VALUE'
    ])
    ->registerRuntimeField(
        null,
        new \Bitrix\Main\Entity\ReferenceField(
            'PROCEDURES_NEW',
            ProceduresTable::getEntity(),
            ['=this.PROCEDURES_NEW_ID' => 'ref.IBLOCK_ELEMENT_ID']
        )
    )
    ->setOrder(['ELEMENT.ID'])
    ->setFilter([]);
*/

$q = DoctorsTable::query()
    ->setSelect([
        'ELEMENT.ID',
        'ELEMENT.NAME',
        'PROCEDURES',
        //'PROCEDURES.ELEMENT',
    ])
    ->setOrder(['ELEMENT.ID'])
    ->setFilter([]);

echo '<pre>';
var_dump($q->getQuery());
echo '<pre>';

$obj = $q->exec()->fetchCollection();

foreach ($obj as $item) {
    echo '<pre>';
    var_dump($item->getProcedures());
    echo '<pre>';
}

