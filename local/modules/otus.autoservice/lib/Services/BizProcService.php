<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Tables\BpWorkflowTemplateTable;

Loc::loadMessages(__FILE__);

class BizProcService
{
    /**
     * Возвращает список шаблонов бизнес процессов для инфоблоков тип список
     *
     * @return Result
     */
    public static function getBizProcTemplates(): Result
    {
        $result = new Result;

        $rows = BpWorkflowTemplateTable::query()
            ->where('MODULE_ID', 'lists')
            ->addOrder('ID', 'DESC')
            ->setSelect(['ID', 'NAME'])
            ->fetchAll();

        if (empty($rows)) {
            return $result->addError(new Error());
        }

        return $result->setData(array_column($rows, 'NAME', 'ID'));
    }
}
