<?php
namespace Otus\Customrest\Tables;

use Bitrix\Crm\ContactTable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\Validators\UniqueValidator;

Loc::loadMessages(__FILE__);

class CarTable extends DataManager
{
    public static function getTableName()
    {
        return 'otus_cars';
    }
    public static function getMap()
    {
        return [
            'ID' => (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true)
                ->configureTitle(Loc::getMessage('ID_NAME')),
            'VIN' => (new StringField('VIN'))
                ->addValidator(
                    (new LengthValidator(null, 17))
                )
                ->addValidator(
                    (new UniqueValidator)
                )
                ->configureTitle(Loc::getMessage('VIN_NAME'))
                ->configureRequired(),

            'BRAND' => (new StringField('BRAND'))
                ->configureTitle(Loc::getMessage('BRAND_NAME'))
                ->configureRequired(),

            'MODEL' => (new StringField('MODEL'))
                ->configureTitle(Loc::getMessage('MODEL_NAME'))
                ->configureRequired(),

            'CONTACT_ID' => (new IntegerField(
                'CONTACT_ID'
            ))->configureTitle(Loc::getMessage('CONTACT_ID_NAME'))
                ->configureRequired(),

            'CONTACT' => (new Reference('CONTACT',
                ContactTable::class,
                Join::on('this.CONTACT_ID', 'ref.ID')
            ))->configureTitle(Loc::getMessage('CONTACT_NAME')),
        ];
    }
}