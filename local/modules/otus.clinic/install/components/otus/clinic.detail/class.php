<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use Otus\Clinic\Services\DoctorService;
use Otus\Clinic\Helpers\IblockHelper;
use Otus\Clinic\Utils\BaseUtils;

class ClinicDetail extends CBitrixComponent
{
    protected static $iblockEntityId = null;
    protected static $referencePropCode = null;

    public function onPrepareComponentParams($arParams)
    {
        $result = [
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => isset($arParams["CACHE_TIME"])? $arParams["CACHE_TIME"]: 36000000,
        ];

        // используем параметры комплексного компонента
        return array_merge($result, $this->__parent->arParams);
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('otus.clinic')) {
            throw new \RuntimeException(Loc::getMessage('ERROR_NOT_INCLUDE_MODULE'));
        }

        Loc::loadMessages(__FILE__);

        self::$iblockEntityId = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_DOCTORS');

        if (!intval(self::$iblockEntityId)) {
            throw new \RuntimeException(Loc::getMessage('ERROR_FATAL_IBL_ID_NULL'));
        }

        self::$referencePropCode = Option::get('otus.clinic', 'OTUS_CLINIC_IBLOCK_PROP_REFERENCE');

        if ($this->startResultCache()) {

            $fields = $this->arParams['DETAIL_FIELD_CODE'];
            $properties = $this->arParams['DETAIL_PROPERTY_CODE'];
            $fields = IblockHelper::prepareFields($fields);
            $properties = array_filter($properties);

            $params['select'] = self::prepareSelectParams($fields, $properties);
            $params['filter'] = ['ELEMENT.ID' => $this->__parent->arVariables['ID']];

            $doctor = DoctorService::getDoctor($fields, $properties, $params);

            if (!$doctor->isSuccess()) {
                ShowError(BaseUtils::extractErrorMessage($doctor));
                $this->abortResultCache();
            }

            $row = $doctor->getData();

            foreach ($row['ITEM'] as $key => $val) {
                switch ($key) {
                    case 'ELEMENT.DETAIL_PICTURE':
                    case 'ELEMENT.PREVIEW_PICTURE': {
                        $file = \CFile::ResizeImageGet($val, ["width"=> 200, "height"=> 200], BX_RESIZE_IMAGE_EXACT, true);
                        $row['ITEM'][$key] = "<br/><img width=\"{$file['width']}\" height=\"{$file['height']}\" alt=\"\" src=\"{$file['src']}\" />";
                        break;
                    }
                }

                // Получаем данные из reference по ID занчений сохраненных в св-ве
                if (is_array($val)) {
                    $row['ITEM'][$key] = implode(', ', $val);
                }
            }

            $this->arResult = $row;
            $this->arResult['NAMES'] = array_merge(IblockHelper::getPropertiesNames($properties), IblockHelper::getFieldNames($fields));

            $this->SetResultCacheKeys([]);
        }

        $this->includeComponentTemplate();

        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage(
            'T_OTUS_DOCTOR_DETAIL_TITLE',
            [
                '#NAME#' => $this->arResult['FIELDS']['NAME'],
            ]
        ));
    }

    private static function prepareSelectParams(array $fields, array $properties): array
    {
        $result = ['PROCEDURES'];

        foreach ($properties as $property) {
            // Для св-ва "связи" не нужно подставлять .VALUE
            if (self::$referencePropCode == $property) {
                $result[$property . '_VALUE'] = $property;
            } else {
                $result[$property . '_VALUE'] = $property . '.VALUE';
            }
        }

        return array_merge($result, $fields);
    }
}