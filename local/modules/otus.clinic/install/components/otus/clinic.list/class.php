<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Otus\Clinic\Utils\BaseUtils;
use Otus\Clinic\Services\DoctorService;
use Otus\Clinic\Helpers\IblockHelper;

class ClinicList extends CBitrixComponent
{
    const GRID_ID = 'otus_clinic_list_grid_id';

    private static $grid;

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
        self::$grid = new Bitrix\Main\Grid\Options(self::GRID_ID);
        $request = Context::getCurrent()->getRequest();

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

		self::$properties = array_filter($this->arParams['LIST_PROPERTY_CODE']);

		$fieldsAndProperties = array_merge(self::$fields, self::$properties);

		$names = self::getNames();

		$gridHeaders = self::prepareHeaders($names);

		$gridFilterFields = self::prepareFilterFields($fieldsAndProperties, $names);

		$gridSortValues = self::prepareSortParams($fieldsAndProperties);

		$gridFilterValues = self::prepareFilterParams($gridFilterFields, $fieldsAndProperties);

        // Page navigation
        $gridNav = self::$grid->GetNavParams();
        $pager = new PageNavigation('page');
        $pager->setPageSize($gridNav['nPageSize']);
        $pager->setRecordCount(DoctorService::getCount($gridFilterValues));
        if ($request->offsetExists('page')) {
            $currentPage = $request->get('page');
            $pager->setCurrentPage($currentPage > 0 ? $currentPage : $pager->getPageCount());
        } else {
            $pager->setCurrentPage(1);
        }

		$doctors = DoctorService::getDoctors(
            [
                'select' => self::prepareSelectParams(),
                'filter' => self::prepareProperties($gridFilterValues),
                'sort' => self::prepareProperties($gridSortValues),
                'limit' => $pager->getLimit(),
                'offset' => $pager->getOffset(),
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
            'PAGINATION' => array(
                'PAGE_NUM' => $pager->getCurrentPage(),
                'ENABLE_NEXT_PAGE' => $pager->getCurrentPage() < $pager->getPageCount(),
                'URL' => $request->getRequestedPage(),
            ),
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

		foreach ($doctors as $key => $item) {
            $template = ($this->arParams['SEF_MODE']=='Y')?
                $this->arParams['SEF_FOLDER'] . '' . $this->arParams['SEF_URL_TEMPLATES']['detail'] : $this->arParams['SEF_FOLDER'] . '?ID=#ID#';

			$viewUrl = CComponentEngine::makePathFromTemplate(
                $template,
				['ID' => $item['ID']]
			);

			$rows[$key] = [
				'id' => $item['ID'],
				'data' => $item,
			];

			foreach ($fieldsAndProperties as $column) {
                $value = "";

                switch ($column) {
                    case 'NAME': {
                        $value = '<a href="' . htmlspecialcharsEx(
                                $viewUrl
                            ) . '" target="_self">' . $item[$column] . '</a>';

                        break;
                    }
                    case 'DETAIL_PICTURE':
                    case 'PREVIEW_PICTURE': {
                        $id = $item['PREVIEW_PICTURE']?: $item[$column];
                        $file = CFile::ResizeImageGet($id, ["width"=> 50, "height"=> 50], BX_RESIZE_IMAGE_EXACT, true);

                        $value = "<img width=\"{$file['width']}\" height=\"{$file['height']}\" alt=\"\" src=\"{$file['src']}\" />";

                        break;
                    }
                    case self::$referencePropCode: {
                        foreach ($item[$column] as $procedureName => $procedureColors) {
                            $value .= "<span class=\"procedure-item\"><b style=\"background-color:{$procedureColors[0]}\"></b>{$procedureName}<span>&nbsp;";
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
		$result = []; //'PROCEDURES_ID'

		foreach (self::$properties as $property) {
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
		$gridSortValues = self::$grid->getSorting();

		$gridSortValues = array_filter(
			$gridSortValues['sort'],
			function ($field) use ($fieldsAndProperties) {
				return in_array($field, $fieldsAndProperties);
			},
			ARRAY_FILTER_USE_KEY
		);

		if (empty($gridSortValues))
		{
			$gridSortValues = ['ID' => 'asc'];
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
