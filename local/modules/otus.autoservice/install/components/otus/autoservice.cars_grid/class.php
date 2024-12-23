<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\IblockService;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Traits\ModuleTrait;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Iblock\Iblock;

Loc::loadMessages(__FILE__);

class CarGrid extends CBitrixComponent
{
    const GRID_ID = 'otus_cars_grid';
	public function executeComponent(): void
	{
        try {
            $request = Context::getCurrent()->getRequest();

            if (!Loader::includeModule('otus.autoservice')) {
                throw new \RuntimeException(Loc::getMessage('OTUS_AUTOSERVICE_FAIL_INCLUDE_MODULE'));
            }

            $carIblockId = Option::get('otus.autoservice', "OTUS_AUTOSERVICE_IB_CARS");

            $carService = new CarService;
            $carIblockService = new IblockService($carIblockId);

            $entityId = (int) $this->arParams['ENTITY_ID'];

            if (!$entityId) {
                throw new \RuntimeException(Loc::getMessage('OTUS_AUTOSERVICE_ENTITY_ID_IS_EMPTY'));
            }

            if (isset($request['car_list'])) {
                $page = explode('page-', $request['car_list']);
                $page = $page[1];
            } else {
                $page = 1;
            }

            // Page navigation
            $totalRowsCount = $carService->getCount($entityId);

            $nav = new PageNavigation('car_list');
            $nav->allowAllRecords(false)->setPageSize($this->arParams['NUM_PAGE'])->initFromUri();
            $nav->setRecordCount($totalRowsCount);

            // Get grid options
            $gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
            $navParams = $gridOptions->GetNavParams();

            $gridColumns= self::getColumns($carIblockService);
            if (!$gridColumns->isSuccess()) {
                throw new \RuntimeException(implode(', ', $gridColumns->getErrorMessages()));
            }

            $limit = $this->arParams['NUM_PAGE']==$navParams['nPageSize']? $this->arParams['NUM_PAGE'] : $navParams['nPageSize'];
            $gridRows = self::getRows($carService, $entityId, $page, $limit);

            if (!$gridRows->isSuccess()) {
                throw new \RuntimeException(implode(', ', $gridRows->getErrorMessages()));
            }

            $this->arResult = [
                'GRID_ID' => self::GRID_ID,
                'COLUMNS' => $gridColumns->getData(),
                'ROWS' => $gridRows->getData(),
                'NAV_OBJECT' => $nav,
                'TOTAL_ROWS_COUNT' => $totalRowsCount,
                'SHOW_ROW_CHECKBOXES' => $this->arParams['SHOW_ROW_CHECKBOXES'],
                'ALLOW_SORT' => true,
            ];

            pLog([__METHOD__ => [
                $this->arResult
            ]]);

            $this->IncludeComponentTemplate();

        } catch (\Throwable $e) {
            ShowError($e->getMessage());
        }
	}

    private function getColumns(IblockService $carIblockService): Result
    {
        $result = new Result;
        $columns = [];

        $iblockProps = $carIblockService->getIblockProperties();

        if (!$iblockProps->isSuccess()) {
            return $result->addError(
                BaseHelper::extractErrorMessage($iblockProps)
            );
        }

        foreach ($iblockProps->getData() as $code => $name) {
            $columns[] = [
                'id' => $code,
                'name' => $name,
                'default' => true
            ];
        }

        return $result->setData($columns);
    }

	private function getRows(CarService $carIblockService, int $contactId, int $page = 1, int $limit = 5): Result
	{
        $result = new Result;
        $data = [];

        if (!$contactId) {
            return $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_ENTITY_ID_IS_EMPTY')));
        }

        $offset = $limit * ($page-1);

        $rows = $carIblockService->getCars($contactId, $offset, $limit);

        if (empty($rows)) {
            return $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_ORDERS_NOT_FOUNT')));
        }

        foreach ($rows as $row) {
            $data[] = [
                'id' => $row['ID'],
                'columns' => $row
            ];
        }

        return $result->setData($data);
	}
}
