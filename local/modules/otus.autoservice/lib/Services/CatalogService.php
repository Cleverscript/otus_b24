<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
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
     * @return array
     */
    public function updateProductQtyRequest(int $reqId): ?array
    {
        $rows = BpCatalogProductsTable::query()
            ->where('REQUEST_ID', $reqId)
            ->setSelect(['ID', 'PROD_ID', 'QTY'])
            ->fetchAll();

        if (empty($rows)) {
            return null;
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

        $this->deleteProductsInBp(array_column($rows, 'ID'));

        return $products;
    }

    /**
     * Отправляет уведомления в колоколец,
     * о кол-ве закупленных позиций по запросу на закупку
     * @param array $products
     * @param string $approverId
     * @param string $creatorId
     * @return Result
     */
    public function sendNotifyAfterUpdateQty(array $products, string $approverId, string $creatorId): Result
    {
        $result = new Result;

        $approverId = intval(preg_replace("/[^0-9]/", '', $approverId));
        $creatorId = intval(preg_replace("/[^0-9]/", '', $creatorId));

        if (empty($products)) {
            $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_PRODUCT_NOT_FOUND')));
        }

        if (!$approverId) {
            $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_APPROVER_ID_NOT_FOUND')));
        }

        if (!$creatorId) {
            $result->addError(new Error(Loc::getMessage('OTUS_AUTOSERVICE_CREATOR_ID_NOT_FOUND')));
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        $message = $this->getProductUpdateMessage($products);

        (new NotificationService)->sendNotification(
            $approverId,
            $creatorId,
            $message
        );

        return $result->setData(['message' => $message]);
    }

    /**
     * Формирует текст сообщения для уведомления о закупленных позициях
     * @param array $products
     * @return string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getProductUpdateMessage(array $products): ?string
    {
        $message = 'Закуплены товары: ';

        foreach ($products as $id => $qty) {
            $row = $this->getProductById($id);

            $message .=  Loc::getMessage("OTUS_AUTOSERVICE_PROD_MEASURE", [
                '#NAME#' => $row['NAME'],
                '#QTY#' => $qty,
            ]);
        }

        return $message;
    }

    /**
     * Удаляет записи из таблицы бизнес процесса "Запрос на закупку"
     * @param array $products
     * @return void
     */
    private function deleteProductsInBp(array $ids): void
    {
        foreach ($ids as $id) {
            $entity = BpCatalogProductsTable::getByPrimary($id)
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