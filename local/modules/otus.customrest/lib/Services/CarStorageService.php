<?php

namespace Otus\Customrest\Services;

use Bitrix\Rest\RestException;
use Otus\Customrest\Tables\CarTable;
use Otus\Customrest\Contracts\EntityStorage;

class CarStorageService implements EntityStorage
{
    public function add($arParams, $navStart, \CRestServer $server): int
    {
        $originDataStoreResult = CarTable::add($arParams);

        if ($originDataStoreResult->isSuccess()) {
            return $originDataStoreResult->getId();
        } else {
            throw new RestException(
                json_encode($originDataStoreResult->getErrorMessages(), JSON_UNESCAPED_UNICODE),
                RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK
            );
        }
    }

    public function list($arParams, $navStart, \CRestServer $server): array
    {
        return CarTable::getList([
            'filter' => $arParams['filter'] ?: [],
            'select' => $arParams['select'] ?: ['*'],
            'order' => $arParams['order'] ? [$arParams['order']['by'] => $arParams['order']['direction']] : ['ID' =>
                'ASC'],
            'group' => $arParams['group'] ?: [],
            'limit' => $arParams['limit'] ?: 0,
            'offset' => $navStart ?: 0,
        ])->fetchAll();
    }

    public function update($arParams, $navStart, \CRestServer $server): int
    {
        $entityId = intval($arParams['ID']);

        unset($arParams['ID']);

        $originDataStoreResult = CarTable::update($entityId, $arParams);

        if ($originDataStoreResult->isSuccess()) {
            return $originDataStoreResult->getId();
        } else {
            throw new RestException(
                json_encode($originDataStoreResult->getErrorMessages(), JSON_UNESCAPED_UNICODE),
                RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK
            );
        }
    }

    public function delete($arParams, $navStart, \CRestServer $server): bool
    {
        $entityId = intval($arParams['ID']);
        $originDataStoreResult = CarTable::delete($entityId);

        if ($originDataStoreResult->isSuccess()) {
            return true;
        } else {
            throw new RestException(
                json_encode($originDataStoreResult->getErrorMessages(), JSON_UNESCAPED_UNICODE),
                RestException::ERROR_ARGUMENT, \CRestServer::STATUS_OK
            );
        }
    }
}