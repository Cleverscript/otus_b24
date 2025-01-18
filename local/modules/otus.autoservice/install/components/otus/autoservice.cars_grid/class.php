<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Services\CarService;
use Otus\Autoservice\Services\IblockService;

Loc::loadMessages(__FILE__);

class CarGrid extends CBitrixComponent
{
    const GRID_ID = 'otus_cars_grid';
	public function executeComponent(): void
	{
        try {
            if ($this->startResultCache(false, [$this->arParams['ENTITY_ID']])) {
                if (!Loader::includeModule('otus.autoservice')) {
                    throw new \RuntimeException(Loc::getMessage('OTUS_AUTOSERVICE_FAIL_INCLUDE_MODULE'));
                }

                $request = Context::getCurrent()->getRequest();

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

                $gridColumns = self::getColumns($carIblockService);
                if (!$gridColumns->isSuccess()) {
                    throw new \RuntimeException(implode(', ', $gridColumns->getErrorMessages()));
                }

                $limit = $this->arParams['NUM_PAGE'] == $navParams['nPageSize'] ? $this->arParams['NUM_PAGE'] : $navParams['nPageSize'];
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

                $this->SetResultCacheKeys([
                    'GRID_ID',
                    'COLUMNS',
                    'ROWS',
                    'NAV_OBJECT',
                    'TOTAL_ROWS_COUNT',
                    'SHOW_ROW_CHECKBOXES',
                    'ALLOW_SORT'
                ]);
            }
        } catch (\Throwable $e) {
            ShowError($e->getMessage());
        }

        $this->IncludeComponentTemplate();
	}

    private function getColumns(IblockService $carIblockService): Result
    {
        $result = new Result;
        $columns = [];

        $iblockProps = $carIblockService->getIblockProperties('CODE');

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
            $params = 'CAR_ID=' . $row['ID'] . '&site=' . \SITE_ID . '&' . \bitrix_sessid_get();

            $data[] = [
                'id' => $row['ID'],
                'columns' => $row,
                'actions' => [
                    [
                        'text' => Loc::getMessage('OTUS_AUTOSERVICE_CAR_SHOW_HISTORY'),
                        'default' => true,
                        'onclick' => "BX.SidePanel.Instance.open('/local/components/otus/autoservice.car_show/lazyload.ajax.php?{$params}', {
                            allowChangeHistory: false,
                            animationDuration: 100,
                            width: 1100,
                            cacheable: false,
                        })",
                    ],
                ]
            ];
        }

        return $result->setData($data);
	}
}
