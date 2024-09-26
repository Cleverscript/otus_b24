<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Otus\Customtab\Models\OrderTable;

class CustomtabGrid extends CBitrixComponent
{
    const GRID_ID = 'otus_customtab_grid';
	public function executeComponent(): void
	{
        $grid = new Bitrix\Main\Grid\Options(self::GRID_ID);
        $request = Context::getCurrent()->getRequest();

        if (!Loader::includeModule('otus.clinic')) {
            throw new \RuntimeException(Loc::getMessage('OTUS_CUSTOMTAB_FAIL_INCLUDE_MODULE'));
        }

        Loc::loadMessages(__FILE__);

        // Page navigation
        $gridNav = $grid->GetNavParams();
        $pager = new PageNavigation('page');
        $pager->setPageSize($gridNav['nPageSize']);
        $pager->setRecordCount(/*DoctorService::getCount($gridFilterValues)*/);

        if ($request->offsetExists('page')) {
            $currentPage = $request->get('page');
            $pager->setCurrentPage($currentPage > 0 ? $currentPage : $pager->getPageCount());
        } else {
            $pager->setCurrentPage(1);
        }

        /*
         *  'limit' => $pager->getLimit(),
            'offset' => $pager->getOffset(),
         */

		$rows = self::getRows();

        if (!$rows->isSuccess()) {
            throw new \RuntimeException(implode(', ', $rows));
        }

        $gridHeaders = [];
        $gridRows = $rows->getData();
        $gridSort = [];
        $gridFilter = [];

		$this->arResult = [
			'GRID_ID' => self::GRID_ID,
			'HEADERS' => $gridHeaders,
			'ROWS' => $gridRows,
			'SORT' => $gridSort,
			'FILTER' => $gridFilter,
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

    private function getHeader(): Result
    {
        $result = new Result;

        $rows = OrderTable::getMap();

        dump($rows);

        return $result->setData($rows);
    }

	private function getRows(): Result
	{
        $result = new Result;

        $rows = OrderTable::query()
            ->setSelect([
                '*',
                'COMPANY_NAME' => 'COMPANY.NAME',
                'CLIENT_NAME' => 'CLIENT.NAME',
            ])
            ->addOrder('ID', 'DESC')
            ->exec()->fetchAll();

        if (empty($rows)) {
            return $result->addError(Loc::getMessage('OTUS_CUSTOMTAB_ORDERS_NOT_FOUNT'));
        }

        return $result->setData($rows);
	}

}
