<?php

namespace Otus\CrmActiviti\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;

class Company
{
    public static function addCompany(array $arFields): Result
    {
        $result = new Result;

        if (empty($arFields)) {
            $result->addError(new Error(Loc::getMessage('SEARCHBYINN_ACTIVITY_ADD_COMP_FIELDS_EMPTY')));
        }

        if (empty($arFields['TITLE'])) {
            $result->addError(new Error(Loc::getMessage('SEARCHBYINN_ACTIVITY_ADD_COMP_TITLE_EMPTY')));
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        return $result->setData(['ID' => (new \CAllCrmCompany())->Add(
            $arFields,
            1,
            ['REGISTER_SONET_EVENT' => 1]
        )]);
    }
}