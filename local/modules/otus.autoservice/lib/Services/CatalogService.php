<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Tables\BpCatalogProductsTable;

Loc::loadMessages(__FILE__);

class CatalogService
{
    private int $iblockId;

    use ModuleTrait;

    public function __construct()
    {
        $this->includeModules();

        $this->iblockId = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_IB_PARTS');
    }

    public function getProductIblockId(): int
    {
        return $this->iblockId;
    }

    /**
     * Возвращает элемент инфоблока каталога crm
     * @param int $id
     * @param array $fields
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getProductById(int $id, array $fields = []): array
    {
        $result = [];

        if (!$id) {
            return $result;
        }

        $fields = array_unique(array_merge($fields, ['ID', 'NAME']));

        $entity = Iblock::wakeUp($this->getProductIblockId())->getEntityDataClass();

        return $entity::query()
            ->where('ID', $id)
            ->setSelect($fields)
            ->fetch();
    }

    public function addProductQtyUpdate(int $reqId, int $prodId, int $qty): void
    {
        if (!$reqId) return;

        if (!$prodId) return;

        BpCatalogProductsTable::add([
            'REQUEST_ID' => $reqId,
            'PROD_ID' => $prodId,
            'QTY' => $qty
        ]);
    }

    /**
     * Изменяет отсаток товара
     * @param int $productId
     * @param int $qty
     * @return void
     */
    private function updateQty(int $productId, int $qty): void
    {
        \CCatalogProduct::Update(
            $productId,
            ['QUANTITY' => $qty]
        );
    }

    /**
     * Обрабатывает записи из таблицы бизнес процесса "Запрос на закупку"
     * в которой содержатся ID товаров и запрошенное кол-во для закупки,
     * с привязкой к ID запроса по которому запущен бизнес процесс
     * @param int $reqId
     * @return void
     */
    public function updateProductQtyRequest(int $reqId): void
    {
        $rows = BpCatalogProductsTable::query()
            ->where('REQUEST_ID', $reqId)
            ->setSelect(['ID', 'PROD_ID', 'QTY'])
            ->fetchAll();

        if (empty($rows)) {
            return;
        }

        $products = array_column($rows, 'QTY', 'PROD_ID');

        foreach ($products as $prodId => $qty) {
            if (\CCatalogSKU::IsExistOffers($prodId)) {
                $dbOffers = \CCatalogSKU::getOffersList(
                    [$prodId],
                    0,
                    [],
                    ['ID', 'QUANTITY'],
                    []
                );

                if (!empty($dbOffers[$prodId])) {
                    foreach ($dbOffers[$prodId] as $offer) {
                        $qty = $qty + $offer['QUANTITY'];

                        $this->updateQty($offer['ID'], $qty);
                    }
                }
            } else {
                $qty = $qty + CCatalogProduct::GetByID($prodId)['QUANTITY'];

                $this->updateQty($prodId, $qty);
            }
        }

        $this->deleteProductsInBp($rows);
    }

    /**
     * Удаляет записи из таблицы бизнес процесса "Запрос на закупку"
     * @param array $products
     * @return void
     */
    private function deleteProductsInBp(array $products): void
    {
        foreach ($products as $product) {
            $entity = BpCatalogProductsTable::getByPrimary($product['ID'])
                ->fetchObject();
            $entity->delete();
        }
    }

    /**
     * Подключает модули
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    private function includeModules(): void
    {
        if (!Loader::includeModule('catalog')) {
            throw new \Exception(Loc::getMessage(
                "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
                ['#MODULE_ID#' => 'catalog']
            ));
        }
    }
}