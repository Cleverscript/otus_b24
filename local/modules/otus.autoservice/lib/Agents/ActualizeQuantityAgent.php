<?php

namespace Otus\Autoservice\Agents;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Catalog\Model\Product;
use Otus\Autoservice\Helpers\BaseHelper;

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

        $cnt = $result->getData()['result'];

        return '\Otus\Autoservice\Agents\ActualizeQuantityAgent::run();';
    }

    private static function getProducts(): array
    {
        Loader::includeModule("catalog");

        $rows = ProductTable::query()
            ->where('TIMESTAMP_X', '<', (new DateTime()))
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