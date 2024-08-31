<?php

namespace Otus\Clinic\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Otus\Clinic\Models\Lists\DoctorsTable;
use Otus\Clinic\Utils\BaseUtils;

class DoctorService
{
    public static function getDoctors(array $params, array $fields, array $properties, int $iblId): Result
    {
        $arData = [];
        $result = new Result;

        if (empty($params['select'])) {
            $result->addError(new Error(
                "No fields specified for selection from the infoblock #{$iblId}"
            ));
        }

        if (empty($params['sort'])) {
            $result->addError(new Error(
                "Sorting fields for selection from the infoblock are not specified #{$iblId}"
            ));
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        $rows = DoctorsTable::query()
            ->setSelect($params['select'])
            ->setOrder($params['sort'])
            ->setFilter($params['filter'])
            ->exec();

        if (empty($rows)) {
            return $result->addError(new Error(
                "There are no elements to display from the infoblock. #{$iblId}"
            ));
        }

        foreach ($rows as $key => $item)
        {
            foreach ($fields as $field) {
                $entityKey = BaseUtils::getFieldKeyByEntityClass(DoctorsTable::class, $field);
                //$field = BaseUtils::getFieldNameElement($field);

                $arData[$key][$field] = $item[$entityKey];
            }

            foreach ($properties as $property) {
                $arData[$key][$property] = $item[$property . '_VALUE'];
            }
        }

        return $result->setData($arData);
    }
}