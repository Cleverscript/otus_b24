<?php
namespace Otus\Autoservice\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class BpWorkflowTemplateTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_bp_workflow_template';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new StringField('NAME'))
                ->configureRequired(),

            (new StringField('MODULE_ID'))
                ->configureRequired()
        ];
    }
}