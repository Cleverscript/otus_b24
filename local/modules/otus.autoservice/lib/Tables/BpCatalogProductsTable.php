<?php
namespace Otus\Autoservice\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

class BpCatalogProductsTable extends DataManager
{
    public static function getTableName()
    {
        return 'otus_autoservice_bp_catalog_products';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new IntegerField('REQUEST_ID'))
                ->configureRequired(),

            (new IntegerField('PROD_ID'))
                ->configureRequired(),

            (new IntegerField('QTY'))
                ->configureRequired(),
        ];
    }
}