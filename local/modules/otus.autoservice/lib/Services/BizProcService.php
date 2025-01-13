<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Otus\Autoservice\Tables\BpWorkflowTemplateTable;

class BizProcService
{
    public static function getBizProcTemplates(): Result
    {
        $result = new Result;

        $rows = BpWorkflowTemplateTable::query()
            ->where('MODULE_ID', 'lists')
            ->addOrder('ID', 'DESC')
            ->setSelect(['ID', 'NAME'])
            ->fetchAll();

        return $result->setData(array_column($rows, 'NAME', 'ID'));
    }
}