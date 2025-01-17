<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Model\Product;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Helpers\BaseHelper;
use Otus\Autoservice\Traits\ModuleTrait;
use Otus\Autoservice\Services\ModuleService;
use Otus\Autoservice\Services\IblockService;
use Otus\Autoservice\Services\CatalogService;

Loc::loadMessages(__FILE__);

class PurchaseRequestService
{
    use ModuleTrait;

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

    /**
     * Создает "Запрос на закупку" в инфоблоке тип список
     *
     * @param array $ids
     * @return void
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function createPurchaseRequest(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        $defaultOptions = Option::getDefaults(self::$moduleId);

        $moduleService = ModuleService::getInstance();

        $bpId = $moduleService->getPropVal('OTUS_AUTOSERVICE_PURCHASE_REQUEST_BP_ID');

        $qty = $moduleService->getPropVal('OTUS_AUTOSERVICE_CATALOG_PART_PURCHASE_REQUEST_QTY') ?? 10;

        $iblockId = $moduleService->getPropVal('OTUS_AUTOSERVICE_IB_REQUESTS');

        $iblockService = new IblockService($iblockId);

        $uid = $defaultOptions['OTUS_AUTOSERVICE_CATALOG_PART_PURCHASE_REQUEST_UID'] ?? 1;

        $props = [
            'QTY' =>  $qty,
            'PARTS' => $ids
        ];

        $fields = [
            'CREATED_BY' => $uid,
            'MODIFIED_BY' => $uid,
            'PROPERTY_VALUES' => $props
        ];

        $addResult = $iblockService->addIblockElement($fields);

        if (!$addResult->isSuccess()) {
            throw new \Exception(BaseHelper::extractErrorMessage($addResult));
        }

        $itemId = $addResult->getData()['ID'];

        $runBbResult = self::runBizProcItem($bpId, $itemId, $uid);

        if (!$runBbResult->isSuccess()) {
            throw new \Exception(BaseHelper::extractErrorMessage($runBbResult));
        }
    }

    /**
     * Запускает определенный шаблон бизнес процесса
     * для элемент аинфоблока тип список "Хапрос на закупку"
     *
     * @param int $bpId
     * @param int $itemId
     * @param int $uid
     * @return Result
     */
    private static function runBizProcItem(int $bpId, int $itemId, int $uid): Result
    {
        $result = new Result;

        if (!$bpId) {
            $result->addError(new Error(Loc::getMessage('PURCHASE_REQUEST_BP_ID_INCORRECT')));
        }

        if (!$itemId) {
            $result->addError(new Error(Loc::getMessage('PURCHASE_REQUEST_ITEM_ID_INCORRECT')));
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        $arErrors = [];
        $arParameters = ["TargetUser" => "user_{$uid}"];

        $wfId = \CBPDocument::startWorkflow(
            $bpId,
            ['lists', 'Bitrix\Lists\BizprocDocumentLists', $itemId],
            $arParameters,
            $arErrors
        );

        if (!empty($arErrors)) {
            foreach ($arErrors as $error) {
                $result->addError(new Error(implode(' - ', $error)));
            }
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        return $result->setData(['ID' => $wfId]);
    }

    /**
     * Обновляет значение остатка товарам (запчастям)
     *
     * @param int $qty
     * @param array $ids
     * @return void
     */
    public static function updateProductQty(int $qty, array $ids): void
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
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getProductIds(): array
    {
        $moduleService = ModuleService::getInstance();
        $prodGroupId = $moduleService->getPropVal('OTUS_AUTOSERVICE_CATALOG_PART_PROD_TYPE');
        $notUsedTimestamptFilter = $moduleService->getPropVal('OTUS_AUTOSERVICE_DEBUG_NOT_USED_TIMESTAMP_X_FILTER_PRODUCT');

        $query = ProductTable::query();

        if ($notUsedTimestamptFilter != 'Y') {
            $query->where('TIMESTAMP_X', '<', (new DateTime(date('d.m.Y') . ' 00:00:00')));
        }

        $rows = $query->where('UF_PRODUCT_GROUP', $prodGroupId)
            ->addSelect('ID')
            ->fetchAll();

        return array_column($rows, 'ID');
    }

    /**
     * Устанавливает зачение в св-во QUANTITY элемента каталога
     *
     * @param int $productId
     * @param int $qty
     * @return void
     */
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

    /**
     * Выполняет HTTP запрос во внешний рандом-сервис
     * который возвращает рандомное число
     *
     * @return Result
     */
    public static function getRandomQty(): Result
    {
        $httpClient = new HttpClient();

        $result = new Result;

        $httpClient->query(
            HttpClient::HTTP_GET,
            self::HOST_RANDOM . '/?' . http_build_query(self::HOST_RANDOM_QUERY),
            []
        );

        if ($httpClient->getStatus() != 200) {
            return $result->addError(new Error(implode(', ', $httpClient->getError())));
        }

        $cnt = rand(0, (int) $httpClient->getResult());

        return $result->setData(['result' => $cnt]);
    }
}
