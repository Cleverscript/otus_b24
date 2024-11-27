<?php

namespace Otus\Customrest\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Rest\RestException;
use Otus\Customrest\Tables\CarTable;
use Otus\Customrest\Contracts\EntityStorage;

class CarStorageService implements EntityStorage
{
    /**
     * @param $arParams - поля и значения для добавляемой сущности
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * @param \CRestServer $server - объект с данными о сервере
     * @return int
     * @throws RestException
     */
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

    /**
     * @param $arParams - параметры для выборки по полям сущности
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * @param \CRestServer $server - объект с данными о сервере
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
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

    /**
     * @param $arParams - поля сущности с значениями которые будут обновлены
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * @param \CRestServer $server - объект с данными о сервере
     * @return int
     * @throws RestException
     */
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

    /**
     * @param $arParams - обязательный ключ ID записи сущности
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * * @param \CRestServer $server - объект с данными о сервере
     * @return bool
     * @throws RestException
     */
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