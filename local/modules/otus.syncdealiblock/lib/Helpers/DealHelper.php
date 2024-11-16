<?php

namespace Otus\SyncDealIblock\Helpers;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Crm\DealTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\Localization\Loc;

/**
 * Класс с хелпер-методами сущности сделка CRM_DEAL
 */
class DealHelper
{
    /**
     * Возвращает массив всех пользовательских св-ств
     * сущности сдлка CRM_DEAL
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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

    /**
     * Проверяет есть ли в полях сделки изменения
     * @param array $fields
     * @param array $arFields
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
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