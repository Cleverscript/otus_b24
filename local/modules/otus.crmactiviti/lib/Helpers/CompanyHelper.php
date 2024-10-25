<?php

namespace Otus\CrmActiviti\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Crm\CompanyTable;
use Bitrix\Main\Localization\Loc;

class CompanyHelper
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

    public static function getCompanyProps(): Result
    {
        $result = new Result;

        global $USER_FIELD_MANAGER;

        return $result->setData(
            array_keys($USER_FIELD_MANAGER->GetUserFields("CRM_COMPANY"))
        );
    }

    public static function isExist(int $inn, string $propCode): ?int
    {
        $rows = CompanyTable::query()
            ->where($propCode, $inn)
            ->setSelect(['ID'])
            ->addOrder('ID', 'DESC')
            ->exec()
            ->fetch();

        return $rows['ID'] ?? false;
    }
}