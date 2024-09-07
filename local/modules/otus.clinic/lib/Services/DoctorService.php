<?php

namespace Otus\Clinic\Services;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Config\Option;
use Otus\Clinic\Models\Lists\DoctorsTable;
use Otus\Clinic\Utils\BaseUtils;

class DoctorService
{
    public static $iblockId;

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

        self::$iblockId = (int) Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_DOCTORS');
        $entity = Iblock::wakeUp(self::$iblockId)->getEntityDataClass();

        $collection = $entity::query()
            ->setSelect(array_merge($params['select'], [
                'PROCEDURES_ID.ELEMENT.NAME',
                'PROCEDURES_ID.ELEMENT.COLOR.VALUE'
            ]))
            ->setOrder($params['sort'])
            ->setFilter($params['filter'])
            ->fetchCollection();

        if (empty($collection)) {
            return $result->addError(new Error(
                "There are no elements to display from the infoblock. #{$iblId}"
            ));
        }

        foreach ($collection as $doctor) {
            $arData[$doctor->getId()]['ID'] = $doctor->getId();
            $arData[$doctor->getId()]['NAME'] = $doctor->getName();

            foreach ($fields as $field) {
                $arData[$doctor->getId()][$field] = $doctor->get($field);
            }

            foreach ($properties as $property) {
                if ($referencePropCode != $property) {
                    $arData[$doctor->getId()][$field] = $doctor->get($property);
                } else {
                    // Если св-во с кодом св-ва указанного для связи инфоблоков
                    foreach ($doctor->get($property) as $procedure) {
                        $procedureName = $procedure->getElement()->getName();
                        $colors = $procedure->getElement()->getColor();

                        foreach ($colors as $color) {
                            $arData[$doctor->getId()][$property][$procedureName][] = $color->getValue();
                        }
                    }
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