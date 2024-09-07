<?php

namespace Otus\Clinic\Helpers;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Otus\Clinic\Utils\BaseUtils;

class IblockHelper
{
    public static function getIblocks(): Result
    {
        $data = [];
        $result = new Result;

        $rows = IblockTable::getList([
            'filter' => [
                'IBLOCK_TYPE_ID' => 'lists',
            ],
            'select' => ['ID', 'NAME']
        ])->fetchAll();

        if (empty($rows)) {
            return $result->addError(new Error('Не удалось получить инфоблоки'));
        }

        foreach ($rows as $row) {
            $data[$row['ID']] = $row['NAME'];
        }

        return $result->setData($data);
    }

    public static function getIblockFields(): array
    {
        $data = [];
        $rsFields = ElementTable::getMap();
        foreach ($rsFields as $fieldCode => $arField) {
            if ($arField->getParameter('title') != '') {
                if (!empty($arSelectFields)) {
                    if (in_array($fieldCode, $arSelectFields)) {
                        $data[$fieldCode] = $arField->getParameter('title');
                    }
                } else {
                    $data[$fieldCode] = $arField->getParameter('title');
                }
            }
        }

        return $data;
    }

    public static function getFieldNames($fields): array
    {
        $data = [];

        $iblockFields = self::getIblockFields();

        foreach ($fields as $field) {
            $data[$field] = $iblockFields[$field];
        }

        return $data;
    }

    public static function getPropertiesNames($properties): array
    {
        $data = [];

        $result = PropertyTable::query()
            ->setSelect(['NAME', 'CODE'])
            ->setFilter(['CODE' => $properties])
            ->exec();

        foreach ($result as $item) {
            $data[$item['CODE']] = $item['NAME'];
        }

        return $data;
    }

    public static function getIblockProps(int $iblockId): Result
    {
        $data = [];
        $result = new Result;

        $rsProp = \CIBlockProperty::GetList(
            [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $iblockId,
            ]
        );
        while ($arr = $rsProp->Fetch()) {
            if (in_array($arr['PROPERTY_TYPE'], ['L', 'N', 'S', 'E'])) {
                $data[$arr['CODE']] = '[' . $arr['CODE'] . '] ' . $arr['NAME'];
            }
        }

        return $result->setData($data);
    }

    /**
     * @param array $fields
     * @return array
     */
    public static function prepareFields(array $fields): array
    {
        $fields = array_filter($fields);

        if (!in_array('ID', $fields)) {
            $fields[] = 'ID';
        }

        return $fields;
    }
}