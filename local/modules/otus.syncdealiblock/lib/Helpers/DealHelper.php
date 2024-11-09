<?php

namespace Otus\SyncDealIblock\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Crm\DealTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\Localization\Loc;

class DealHelper
{
    public static function getDealProps(): Result
    {
        $data = [];
        $result = new Result;

        $entityId = 'CRM_DEAL';
        $dbUserFields = UserFieldTable::getList([
            'filter' => ['ENTITY_ID' => $entityId]
        ]);

        while ($arUF = $dbUserFields->fetch()) {
            $dbUFLang = UserFieldLangTable::getList([
                'filter' => ['USER_FIELD_ID' => $arUF['ID']]
            ]);

            while ($arUFLang = $dbUFLang->fetch()) {
                if (LANGUAGE_ID == $arUFLang['LANGUAGE_ID']) {
                    $data[$arUF['ID']] = [
                        'ID' => $arUF['ID'],
                        'CODE' => $arUF['FIELD_NAME'],
                        'NAME' => $arUFLang['EDIT_FORM_LABEL']
                    ];
                }
            }
        }

        return $result->setData($data);
    }

    public static function diffChangePropsVals(array $fields, array $arFields): array
    {
        $result = [];
        $dealId = $arFields['ID'];

        $dealInfo = DealTable::query()
            ->where('ID', $dealId)
            ->setSelect($fields)
            ->exec()->fetch();

        foreach ($fields as $fieldCode) {
            if (empty($arFields[$fieldCode]) || $dealInfo[$fieldCode] === $arFields[$fieldCode]) continue;

            $result[$fieldCode] = $arFields[$fieldCode];
        }

        return $result;
    }
}