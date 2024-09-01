<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Otus\Clinic\Utils\BaseUtils;
use Otus\Clinic\Services\DoctorService;
use Otus\Clinic\Helpers\IblockHelper;

class ClinicList extends CBitrixComponent
{
    const GRID_ID = 'otus_clinic_list_grid_id';

	public static array $fields = [];

	public static array $properties = [];

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

	public function executeComponent(): void
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


		self::$fields = IblockHelper::prepareFields($this->arParams['LIST_FIELD_CODE']);

		self::$properties = array_filter($this->arParams['LIST_PROPERTY_CODE'], fn($value) => $value !== '');

		$fieldsAndProperties = array_merge(self::$fields, self::$properties);

		$names = self::getNames();

		$gridHeaders = self::prepareHeaders($names);

		$gridFilterFields = self::prepareFilterFields($fieldsAndProperties, $names);

		$gridSortValues = self::prepareSortParams($fieldsAndProperties);

		$gridFilterValues = self::prepareFilterParams($gridFilterFields, $fieldsAndProperties);

		$doctors = DoctorService::getDoctors(
            [
                'select' => self::prepareSelectParams(),
                'filter' => self::prepareProperties($gridFilterValues),
                'sort' => self::prepareProperties($gridSortValues),
            ],
            self::$fields,
            self::$properties,
            self::$iblockEntityId
        );

        if (!$doctors->isSuccess()) {
            throw new \RuntimeException(BaseUtils::extractErrorMessage($doctors));
        }

		$rows = self::getRows($doctors, $fieldsAndProperties);

        if (!$rows->isSuccess()) {
            throw new \RuntimeException(BaseUtils::extractErrorMessage($rows));
        }

		$this->arResult = [
			'GRID_ID' => self::GRID_ID,
			'HEADERS' => $gridHeaders,
			'ROWS' => $rows->getData(),
			'SORT' => $gridSortValues,
			'FILTER' => $gridFilterFields,
			'ENABLE_LIVE_SEARCH' => false,
			'DISABLE_SEARCH' => true,
		];

		$this->IncludeComponentTemplate();
	}

	private function getRows(Result $doctors, array $fieldsAndProperties): Result
	{
		$rows = [];
        $result = new Result;

        if (!$doctors->isSuccess()) {
            return $result->addError(new Error(BaseUtils::extractErrorMessage($doctors)));
        }

        $doctors = $doctors->getData();
        //echo '<pre>';
        //var_dump($doctors);
        //echo '<pre>';

		foreach ($doctors as $key => $item) {
            // Формируем ссылку на детальную страницу
            $template = ($this->arParams['SEF_MODE']=='Y')?
                $this->arParams['SEF_FOLDER'] . '' . $this->arParams['SEF_URL_TEMPLATES']['detail'] : $this->arParams['SEF_FOLDER'] . '?ID=#ID#';

			$viewUrl = CComponentEngine::makePathFromTemplate(
                $template,
				['ID' => $item['ELEMENT.ID']]
			);

			$rows[] = [
				'id' => $item['ELEMENT.ID'],
				'data' => $item,
			];

			foreach ($fieldsAndProperties as $column) {
                switch ($column) {
                    case 'ELEMENT.NAME': {
                        $value = '<a href="' . htmlspecialcharsEx(
                                $viewUrl
                            ) . '" target="_self">' . $item['ELEMENT.NAME'] . '</a>';

                        break;
                    }
                    case 'ELEMENT.DETAIL_PICTURE':
                    case 'ELEMENT.PREVIEW_PICTURE': {
                        $id = $item['ELEMENT.PREVIEW_PICTURE']?: $item['ELEMENT.DETAIL_PICTURE'];
                        $file = CFile::ResizeImageGet($id, ["width"=> 50, "height"=> 50], BX_RESIZE_IMAGE_EXACT, true);

                        $value = "<img width=\"{$file['width']}\" height=\"{$file['height']}\" alt=\"\" src=\"{$file['src']}\" />";

                        break;
                    }
                    default: {
                        $value = $item[$column];
                    }
                }

                // Получаем данные из reference по ID занчений сохраненных в св-ве
                if (is_array($value)) {
                    $multiPropVals = [];
                    foreach ($value as $id) {
                        $multiPropVals[] = $doctors[$key][str_replace('_ID', '', self::$referencePropCode)][$id];
                    }
                    $value = implode(', ', array_filter($multiPropVals));
                }
				
				$rows[$key]['columns'][$column] = $value;
			}
		}

		return $result->setData($rows);
	}

	private static function prepareProperties(array $props): array
	{
		if (empty($props)) {
            return $props;
        }

		$result = [];

		foreach ($props as $key => $value) {
			if (in_array($key, self::$properties)) {
				$result[$key . '_VALUE'] = $value;
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}

	private static function prepareSelectParams(): array
	{
		$result = ['PROCEDURES'];

		foreach (self::$properties as $property) {
            // Для св-ва "связи" не нужно подставлять .VALUE
            if (self::$referencePropCode == $property) {
                $result[$property . '_VALUE'] = $property;
            } else {
                $result[$property . '_VALUE'] = $property . '.VALUE';
            }
		}

		return array_merge($result, self::$fields);
	}

	private static function prepareSortParams(array $fieldsAndProperties): array
	{
		$grid = new Bitrix\Main\Grid\Options(self::GRID_ID);

		$gridSortValues = $grid->getSorting();

		$gridSortValues = array_filter(
			$gridSortValues['sort'],
			function ($field) use ($fieldsAndProperties) {
				return in_array($field, $fieldsAndProperties);
			},
			ARRAY_FILTER_USE_KEY
		);

		if (empty($gridSortValues))
		{
			$gridSortValues = ['ELEMENT.ID' => 'asc'];
		}

		return $gridSortValues;
	}

	private static function prepareFilterParams($filterFields, $fieldsAndProperties): array
	{
		$gridFilter = new Bitrix\Main\UI\Filter\Options(self::GRID_ID);
		$gridFilterValues = $gridFilter->getFilter($filterFields);

		return array_filter(
			$gridFilterValues,
			function ($fieldName) use ($fieldsAndProperties) {
				return in_array($fieldName, $fieldsAndProperties);
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	private static function prepareHeaders($names): array
	{
		$headers = [];

		foreach ($names as $field => $name) {
			$headers[] = [
				'id' => $field,
				'name' => $name,
				'sort' => $field,
				'first_order' => 'desc',
				'default' => true,
			];
		}

		return $headers;
	}

	private static function prepareFilterFields(array $fieldsAndProperties, array $names): array
	{
		$filterFields = [];

		foreach ($fieldsAndProperties as $field) {
			if (!empty($field)) {
				$filterFields[] = [
					'id' => $field,
					'name' => $names[$field],
					'sort' => $field,
					'first_order' => 'desc',
					'default' => true,
				];
			}
		}

		return $filterFields;
	}

	private static function getNames(): array
	{
		$fieldNames = IblockHelper::getFieldNames(self::$fields);
		$propertiesNames = IblockHelper::getPropertiesNames(self::$properties);

		return array_merge($fieldNames, $propertiesNames);
	}
}
