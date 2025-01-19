<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Result;
use Bitrix\Crm\DealTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Crm\Category\DealCategory;

Loc::loadMessages(__FILE__);

class DealService
{
    protected $moduleService;
    public string $propCarCode;

    public function __construct()
    {
        $this->moduleService = ModuleService::getInstance();
        $this->propCarCode = $this->moduleService->getPropVal('OTUS_AUTOSERVICE_DEAL_PROP_CAR');

        if (empty($this->propCarCode)) {
            throw new \Exception(Loc::getMessage('OTUS_AUTOSERVICE_PROP_CAR_CODE_EMPTY'));
        }
    }

    /**
     * Возвращает код-во сделок по автомобилю
     *
     * @param int $carId
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCountByCar(int $carId): int
    {
        if (!$carId) {
            return 0;
        }

        return DealTable::query()
            ->where($this->propCarCode, $carId)
            ->exec()
            ->getSelectedRowsCount();
    }

    /**
     * Возвращает не ID не закрытой сделки по автомобилю
     *
     * @param int $carId
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getOpenDealByCar(int $carId): ?int
    {
        if (!$carId) {
            return 0;
        }

        return DealTable::query()
            ->where($this->propCarCode, $carId)
            ->addSelect('ID')
            ->where('CLOSED', 'N')
            ->fetch()['ID'];
    }

    /**
     * Возвращает сделки по автомобилю
     *
     * @param int $carId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDeals(int $carId, int $offset = 0, int $limit = 5): array
    {
        if (!$carId) {
            return 0;
        }

        $result = [];

        $rows = DealTable::query()
            ->where($this->propCarCode, $carId)
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

    /**
     * Возвращает наименование Сделки
     *
     * @param int $dealId
     * @return string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDealName(int $dealId): ?string
    {
        return DealTable::query()
            ->where('ID', $dealId)
            ->addSelect('TITLE')
            ->fetch()['TITLE'];
    }

    /**
     * Возвращает ID ответсвенного по Сделке
     *
     * @param int $dealId
     * @return string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDealAssigned(int $dealId): ?string
    {
        return DealTable::query()
            ->where('ID', $dealId)
            ->addSelect('ASSIGNED_BY_ID')
            ->fetch()['ASSIGNED_BY_ID'];
    }

    /**
     * Возвращает ID категории (воронки) сделки
     *
     * @param int $dealId
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDealCategoryId(int $dealId): int
    {
        return DealTable::query()
            ->where('ID', $dealId)
            ->addSelect('CATEGORY_ID')
            ->fetch()['CATEGORY_ID'] ?: 0;
    }

    /**
     * Возвращает массив всех пользовательских св-ств
     * сущности сдлка CRM_DEAL
     *
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDealProps(): Result
    {
        $data = [];
        $result = new Result;

        $entityId = 'CRM_DEAL';
        $dbUserFields = UserFieldTable::getList([
            'filter' => ['ENTITY_ID' => $entityId]
        ]);

        while ($arUF = $dbUserFields->fetch()) {
            $dbUFLang = UserFieldLangTable::getList([
                'filter' => ['USER_FIELD_ID' => $arUF['ID']]
            ]);

            while ($arUFLang = $dbUFLang->fetch()) {
                if (LANGUAGE_ID == $arUFLang['LANGUAGE_ID']) {
                    $data[$arUF['ID']] = [
                        'ID' => $arUF['ID'],
                        'CODE' => $arUF['FIELD_NAME'],
                        'NAME' => $arUFLang['EDIT_FORM_LABEL']
                    ];
                }
            }
        }

        return $result->setData($data);
    }

    /**
     * Возвращает массив со списком категорий (воронок) сделок)
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getCategories(): array
    {
        return array_column(DealCategory::getAll(true), 'NAME', 'ID') ?: [];
    }
}
