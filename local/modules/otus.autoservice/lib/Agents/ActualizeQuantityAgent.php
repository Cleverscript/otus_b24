<?php
namespace Otus\Autoservice\Agents;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Model\Product;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\CatalogService;

class ActualizeQuantityAgent
{
    const HOST_RANDOM = 'https://www.random.org/integers/';
    const HOST_RANDOM_QUERY  = [
            'num' => 1,
            'min' => 0,
            'max' => 10,
            'col' => 1,
            'base' => 10,
            'format' => 'plain',
            'rnd' => 'new'
        ];

    public static function run()
    {
        $result = self::getRandom();

        if (!$result->isSuccess()) {
            throw new \Exception(BaseHelper::extractErrorMessage($result));
        }

        $qty = $result->getData()['result'];

        $rows = self::getProducts();

        if (!empty($rows)) {
            if ($qty === 0) {
                self::createPurchaseRequest($rows);
            } else {
                self::updateProductQty($qty, $rows);
            }
        }

        return '\Otus\Autoservice\Agents\ActualizeQuantityAgent::run();';
    }

    private static function createPurchaseRequest(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        $qty = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_CATALOG_PART_PURCHASE_REQUEST_QTY');

        //pLog([__METHOD__ => [$ids, $qty]]);
    }

    /**
     * Обновляет значение остатка товарам (запчастям)
     * @param int $qty
     * @param array $ids
     * @return void
     */
    private static function updateProductQty(int $qty, array $ids): void
    {
        if (!$qty) {
            return;
        }

        if (empty($ids)) {
            return;
        }

        $catalogService = new CatalogService;

        foreach ($ids as $prodId) {
            $catalogService->updateProductQty($prodId, $qty, false);
            $catalogService->updateProductTimestampt($prodId);
        }
    }

    /**
     * Возвращает товары (запчасти),
     * которые еще не были одновлены на сегодняшнюю дату
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function getProducts(): array
    {
        if (!Loader::includeModule("catalog")) {
            return [];
        }

        $prodGroupId = ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_CATALOG_PART_PROD_TYPE');

        $rows = ProductTable::query()
            ->where('TIMESTAMP_X', '<', (new DateTime(date('d.m.Y'). ' 00:00:00')))
            ->where('UF_PRODUCT_GROUP', $prodGroupId)
            ->addSelect('ID')
            ->fetchAll();

        return array_column($rows, 'ID');
    }

    private static function setQty(int $productId, int $qty): void
    {
        if (!$productId) {
            return;
        }

        Product::Update(
            $productId,
            [
                'QUANTITY' => $qty
            ]
        );
    }

    private static function getRandom(): Result
    {
        $httpClient = new HttpClient();

        $result = new Result;

        $httpClient->query(
            HttpClient::HTTP_GET,
            self::HOST_RANDOM . '/?' . http_build_query(self::HOST_RANDOM_QUERY),
            []
        );

        if ($httpClient->getStatus() != 200) {
            $result->addError(new Error(implode(', ', $httpClient->getError())));
        }

        $cnt = rand(0, $httpClient->getResult());

        return $result->setData(['result' => $cnt]);
    }
}