<?php
namespace Otus\Models;

use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

class OrderTable extends DataManager
{
    public static function getTableName()
    {
        return 'otus_order';
    }
    public static function getMap()
    {
        return [
            'ID' => (new IntegerField(
                'ID',
                []
            ))->configurePrimary(true)
                ->configureAutocomplete(true),

            'TITLE' => (new StringField('TITLE',
                [
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                ]
            )),

            'COMPANY_ID' => (new IntegerField(
                'COMPANY_ID',
                []
            )),

            'CLIENT_ID' => (new IntegerField(
                'CLIENT_ID',
                []
            )),

            'COMPANY' => (new Reference('COMPANY',
                \Bitrix\Iblock\Elements\ElementCompanyTable::class,
                Join::on('this.COMPANY_ID', 'ref.ID')
            )),

            'CLIENT' => (new Reference('CLIENT',
                \Bitrix\Iblock\Elements\ElementClientsTable::class,
                Join::on('this.CLIENT_ID', 'ref.ID')
            )),
        ];
    }
}