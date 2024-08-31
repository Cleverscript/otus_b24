<?php

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Iblock;

class GridDetail extends CBitrixComponent
{
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
		if ($this->startResultCache()) {

			$fields = $this->arParams['DETAIL_FIELD_CODE'];
			$properties = $this->arParams['DETAIL_PROPERTY_CODE'];
			$fields = array_filter($fields);
			$properties = array_filter($properties);
	
			//debug($fields);
			//debug($this->arParams);
	
			$params['select'] = self::prepareSelectParams($fields, $properties);
			$params['filter'] = $this->__parent->arVariables;

			$names = self::getPropertyNames($properties, $fields);

			$company = self::getCompany($fields, $properties, $params);

			if (empty($company)) {
				ShowError(Loc::getMessage('TN_TEST_COMPANIES_NOT_FOUND'));
				$this->abortResultCache();
			}

			$this->arResult = $company;
			$this->arResult['NAMES'] = $names;

			$this->SetResultCacheKeys([]);
		}

		$this->includeComponentTemplate();

		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage(
			'TN_TEST_COMPANIES_SHOW_TITLE',
			[
				'#NAME#' => $this->arResult['FIELDS']['NAME'],
			]
		));
	}

	private static function prepareSelectParams(array $fields, $properties): array
	{
		$result = [];

		foreach ($properties as $property) {
			$result[$property . '_VALUE'] = $property . '.VALUE';
		}

		return array_merge($result, $fields);
	}

	private function getCompany(array $fields, array $properties, array $params): array
	{
		$iblock = Iblock::wakeUp($this->arParams['IBLOCK_ID'])->getEntityDataClass();

		$result = $iblock::query()
			->setSelect($params['select'])
			->setFilter($params['filter'])
			->exec();

		$company = [];

		foreach ($result as $item) {
			foreach ($fields as $field) {
				$company['FIELDS'][$field] = $item[$field];
			}

			foreach ($properties as $property) {
				$company['PROPERTIES'][$property] = $item[$property . '_VALUE'];
			}
		}

		return $company;
	}

	private static function getPropertyNames(array $properties, array $fields): array
	{
		$names = [];
		
		$result = PropertyTable::query()
			->setSelect(['NAME', 'CODE'])
			->setFilter(['CODE' => $properties])
			->exec();

		foreach ($result as $item) {
			$names[$item['CODE']] = $item['NAME'];
		}

		foreach ($fields as $field) {
			$names[$field] = Loc::getMessage('IBLOCK_FIELD_' . $field);
		}

		return $names;
	}
}