<?php

namespace Otus\Clinic\Services;

use Bitrix\Bizproc\Workflow\Template\Packer\Result\Pack;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;
use Otus\Clinic\Models\Lists\DoctorsTable;
use Otus\Clinic\Utils\BaseUtils;

class DoctorService
{
    public static function getDoctors(array $params, array $fields, array $properties, int $iblId): Result
    {
        $arData = [];
        $result = new Result;
        $referencePropCode = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_PROP_REFERENCE');

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

        echo '<pre>';
        var_dump($params['select']);
        echo '<pre>';

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
                // наименования эл-тов по reference также прокидываем в результ массив
                if ($referencePropCode == $property) {
                    $refPropCode = str_replace('_ID', '', $property);
                    $arData[$key][$refPropCode] = $item[$refPropCode];
                    $arData[$key][$property] = $item[$property . '_VALUE'];
                } else {
                    $arData[$key][$property] = $item[$property . '_VALUE'];
                }
            }
        }

        return $result->setData($arData);
    }

    public static function getDoctor(array $fields, array $properties, array $params): Result
    {
        $data = [];
        $result = new Result;
        $referencePropCode = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_PROP_REFERENCE');

        $rows = DoctorsTable::query()
            ->setSelect($params['select'])
            ->setFilter($params['filter'])
            ->exec();

        if (empty($rows)) {
            return $result->addError(new Error("Element not found"));
        }

        foreach ($rows as $item) {
            foreach ($fields as $field) {
                $entityKey = BaseUtils::getFieldKeyByEntityClass(DoctorsTable::class, $field);
                $data['ITEM'][$field] = $item[$entityKey];
            }

            foreach ($properties as $property) {
                // наименования эл-тов по reference также прокидываем в результ массив
                if ($referencePropCode == $property) {
                    $refPropCode = str_replace('_ID', '', $property);
                    $data['ITEM'][$refPropCode] = $item[$refPropCode];
                    //$data['ITEM'][$property] = $item[$property . '_VALUE'];
                } else {
                    $data['ITEM'][$property] = $item[$property . '_VALUE'];
                }
            }
        }

        return $result->setData($data);
    }
}