<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;

class DealService
{
    public function __construct()
    {
        $this->includeModules();
    }

    public function getCountByCar(int $carId): int
    {
        if (!$carId) {
            return 0;
        }

        return DealTable::query()
            ->where('UF_CAR', $carId)
            ->exec()
            ->getSelectedRowsCount();
    }

    public function getDeals(int $carId, int $offset = 0, int $limit = 5): array
    {
        if (!$carId) {
            return 0;
        }

        $result = [];

        $userService = new UserService;

        $rows = DealTable::query()
            ->where('UF_CAR', $carId)
            ->setSelect([
                'ID',
                'TITLE',
                'CREATED_BY_ID',
                'ASSIGNED_BY_ID',
                'BEGINDATE',
                'OPPORTUNITY_ACCOUNT',
                'CLOSED'
            ])
            ->setLimit($limit)
            ->setOffset($offset)
            ->exec();

        foreach ($rows as $row) {
            $result[] = [
                'ID' => $row['ID'],
                'TITLE' => $row['TITLE'],
                'CREATED_BY_ID' => $row['CREATED_BY_ID'],
                'ASSIGNED_BY_ID' => $row['ASSIGNED_BY_ID'],
                'BEGINDATE' => $row['BEGINDATE'],
                'SUMM' => $row['OPPORTUNITY_ACCOUNT'],
                'CLOSED' => $row['CLOSED'],
            ];
        }

        return $result;
    }

    private function includeModules(): void
    {
        if (!Loader::includeModule('crm')) {
            throw new \Exception(Loc::getMessage(
                "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
                ['#MODULE_ID#' => 'crm']
            ));
        }
    }
}