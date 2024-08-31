<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\PropertyTable;
use Otus\Clinic\Utils\BaseUtils;
use Otus\Clinic\Services\DoctorService;

class ClinicList extends CBitrixComponent
{
    const GRID_ID = 'otus_clinic_list_grid_id';

	public static array $fields = [];

	public static array $properties = [];

    protected static $iblockEntityId = null;

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

		self::$fields = array_filter(self::prepareFields($this->arParams['LIST_FIELD_CODE']), fn($value) => $value !== '');

		self::$properties = array_filter($this->arParams['LIST_PROPERTY_CODE'], fn($value) => $value !== '');

		$fieldsAndProperties = array_merge(self::$fields, self::$properties);

		$names = self::getNames();

		$gridHeaders = self::prepareHeaders($names);

		$gridFilterFields = self::prepareFilterFields($fieldsAndProperties, $names);

		$gridSortValues = self::prepareSortParams($fieldsAndProperties);

		$gridFilterValues = self::prepareFilterParams($gridFilterFields, $fieldsAndProperties);

		$doctors = DoctorService::getDoctors(
            self::prepareQueryParams($gridFilterValues, $gridSortValues),
            self::$fields,
            self::$properties,
            self::$iblockEntityId
        );

        if (!$doctors->isSuccess()) {
            throw new \RuntimeException(BaseUtils::extractErrorMessage($doctors));
        }

		$rows = self::getRows($doctors, $fieldsAndProperties);

		$this->arResult = [
			'COMPANIES' => $doctors->getData(),
			'GRID_ID' => self::GRID_ID,
			'HEADERS' => $gridHeaders,
			'ROWS' => $rows,
			'SORT' => $gridSortValues,
			'FILTER' => $gridFilterFields,
			'ENABLE_LIVE_SEARCH' => false,
			'DISABLE_SEARCH' => true,
		];

		$this->IncludeComponentTemplate();
	}

	private function getRows(Result $doctors, array $fieldsAndProperties): array
	{
		$rows = [];

		foreach ($doctors->getData() as $key => $item) {
            // Формируем ссылку на детальную страницу
            $template = ($this->arParams['SEF_MODE']=='Y')?
                $this->arParams['SEF_URL_TEMPLATES']['detail'] : $this->arParams['SEF_FOLDER'] . '?ID=#ID#';

			$viewUrl = CComponentEngine::makePathFromTemplate(
                $template,
				['ID' => $item['ID']]
			);

			$rows[] = [
				'id' => $item['ID'],
				'data' => $item,
			];

			foreach ($fieldsAndProperties as $column) {
                switch ($column) {
                    case 'NAME': {
                        $value = '<a href="' . htmlspecialcharsEx(
                                $viewUrl
                            ) . '" target="_self">' . $item['NAME'] . '</a>';

                        break;
                    }
                    case 'DESCRIPTION': {
                        $value = null;
                        if (!empty($item['DESCRIPTION'])) {
                            $value = unserialize($item['DESCRIPTION'])['TEXT'] ?: null;
                        }

                        break;
                    }
                    default: {
                        $value = $item[$column];
                    }
                }
				
				$rows[$key]['columns'][$column] = $value;
			}
		}

		return $rows;
	}

    /**
     * К стандартным полям ИБ добавляем ELEMENT что бы работал Query
     * @param array $fields
     * @return array
     */
    private static function prepareFields(array $fields): array
    {
        $fields = array_filter($fields);

        if (!in_array('ID', $fields)) {
            $fields[] = 'ID';
        }

        $fields = array_map(fn($value) => "ELEMENT.{$value}", $fields);

        return $fields;
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
			$result[$property . '_VALUE'] = $property . '.VALUE';
		}

		return array_merge($result, self::$fields);
	}

	private static function prepareQueryParams(array $gridFilterValues, array $gridSortValues): array
	{
		return [
			'select' => self::prepareSelectParams(),
			'filter' => self::prepareProperties($gridFilterValues),
			'sort' => self::prepareProperties($gridSortValues),
		];
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

	private static function getFieldNames(): array
	{
		$names = [];

		foreach (self::$fields as $field) {
			$names[$field] = Loc::getMessage('IBLOCK_FIELD_' . $field);
		}

		return $names;
	}
	private static function getPropertiesNames(): array
	{
		$names = [];

		$result = PropertyTable::query()
			->setSelect(['NAME', 'CODE'])
			->setFilter(['CODE' => self::$properties ])
			->exec();

		foreach ($result as $item) {
			$names[$item['CODE']] = $item['NAME'];
		}

		return $names;
	}

	private static function getNames(): array
	{
		$fieldNames = self::getFieldNames();
		$propertiesNames = self::getPropertiesNames();

		return array_merge($fieldNames, $propertiesNames);
	}
}
