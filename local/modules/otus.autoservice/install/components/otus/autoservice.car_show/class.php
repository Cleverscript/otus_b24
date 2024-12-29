<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Type\Date;
use Otus\Autoservice\Services\UserService;
use Otus\Autoservice\Services\DealService;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;

Loc::loadMessages(__FILE__);

class CarShowGrid extends CBitrixComponent
{
    const GRID_ID = 'otus_car_show_grid';
	public function executeComponent(): void
	{
        try {
            $this->arParams['NUM_PAGE'] = $this->arParams['NUM_PAGE']?: 20;

            $request = Context::getCurrent()->getRequest();

            if (!Loader::includeModule('otus.autoservice')) {
                throw new \RuntimeException(Loc::getMessage('OTUS_AUTOSERVICE_FAIL_INCLUDE_MODULE'));
            }

            $dealService = new DealService;

            $carId = (int) $request->get('CAR_ID');

            if (!$carId) {
                throw new \RuntimeException(Loc::getMessage('OTUS_AUTOSERVICE_ENTITY_ID_IS_EMPTY'));
            }

            if (isset($request['car_show_list'])) {
                $page = explode('page-', $request['car_show_list']);
                $page = $page[1];
            } else {
                $page = 1;
            }

            // Page navigation
            $totalRowsCount = $dealService->getCountByCar($carId);

            $nav = new PageNavigation('car_show_list');
            $nav->allowAllRecords(false)->setPageSize($this->arParams['NUM_PAGE'])->initFromUri();
            $nav->setRecordCount($totalRowsCount);

            // Get grid options
            $gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
            $navParams = $gridOptions->GetNavParams();

            $limit = $this->arParams['NUM_PAGE']==$navParams['nPageSize']? $this->arParams['NUM_PAGE'] : $navParams['nPageSize'];

            $gridRows = self::getRows($dealService, $carId, $page, $limit);

            if (!$gridRows->isSuccess()) {
                throw new \RuntimeException(implode(', ', $gridRows->getErrorMessages()));
            }

            $this->arResult = [
                'GRID_ID' => self::GRID_ID,
                'COLUMNS' => self::getColumns(),
                'ROWS' => $gridRows->getData(),
                'NAV_OBJECT' => $nav,
                'TOTAL_ROWS_COUNT' => $totalRowsCount,
                'SHOW_ROW_CHECKBOXES' => 'N',
                'ALLOW_SORT' => true,
            ];

            $this->IncludeComponentTemplate();

        } catch (\Throwable $e) {
            ShowError($e->getMessage());
        }
	}

    private function getColumns(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'default' => true
            ],
            [
                'id' => 'TITLE',
                'name' => 'Название',
                'default' => true
            ],
            [
                'id' => 'CREATED_BY_ID',
                'name' => 'Создал',
                'default' => true
            ],
            [
                'id' => 'ASSIGNED_BY_ID',
                'name' => 'Ответственный',
                'default' => true
            ],
            [
                'id' => 'BEGINDATE',
                'name' => 'Дата',
                'default' => true
            ],
            [
                'id' => 'SUMM',
                'name' => 'Сумма',
                'default' => true
            ],
            [
                'id' => 'CLOSED',
                'name' => 'Закрыта',
                'default' => true
            ],
        ];
    }

	private function getRows(DealService $dealService, int $carId, int $page = 1, int $limit = 5): Result
	{
        $result = new Result;
        $data = [];

        if (!$carId) {
            return $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_ENTITY_ID_IS_EMPTY')));
        }

        $offset = $limit * ($page-1);

        $userService = new UserService;

        $rows = $dealService->getDeals($carId, $offset, $limit);

        if (empty($rows)) {
            return $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_ORDERS_NOT_FOUNT')));
        }

        foreach ($rows as $row) {
            foreach ($row as $key => $val) {
                switch ($key) {
                    case 'TITLE': {
                        $val = "<a href=\"/crm/deal/details/{$row['ID']}/\">{$val}</a>";

                        break;
                    }
                    case 'CREATED_BY_ID':
                    case 'ASSIGNED_BY_ID': {
                        $fullName = $userService->getFullName($val);
                        $val = "<a href=\"/company/personal/user/{$val}/\">{$fullName}</a>";

                        break;
                    }
                    case 'BEGINDATE': {
                        $val = $val instanceof Date ? $val->format('d.m.Y') : $val;

                        break;
                    }
                    case 'CLOSED': {
                        $val = $val == 'Y' ? Loc::getMessage('OTUS_AUTOSERVICE_YES') : Loc::getMessage('OTUS_AUTOSERVICE_NO');

                        break;
                    }
                }

                $row[$key] = $val;
            }

            $data[] = [
                'id' => $row['ID'],
                'columns' => $row
            ];
        }

        return $result->setData($data);
	}
}
