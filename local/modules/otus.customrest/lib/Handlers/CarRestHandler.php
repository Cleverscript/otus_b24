<?php

namespace Otus\Customrest\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Rest\RestException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\DI\ServiceLocator;
use Psr\Container\NotFoundExceptionInterface;

Loc::loadMessages(__FILE__);

Loader::includeModule('rest');

class CarRestHandler
{
    const SCOPE = 'otus.customrest.car';

    protected static $service = 'otus.customrest.car.storage';

    /**
     * Хендлер метод обработчика события регистрации REST методов,
     * добавляет в систему перечень кастомных методов REST
     * @return array[]
     */
    public static function carRestMethodsRegistration()
    {
        Loc::getMessage('REST_SCOPE_OTUS.CUSTOMREST_CAR');

        return [
            static::SCOPE => [
                'otus.customrest.car.add' => [__CLASS__, 'add'],
                'otus.customrest.car.list' => [__CLASS__, 'list'],
                'otus.customrest.car.update' => [__CLASS__, 'update'],
                'otus.customrest.car.delete' => [__CLASS__, 'delete'],
            ]
        ];
    }

    /**
     * @param $arParams - поля и значения для добавляемой сущности
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * @param \CRestServer $server - объект с данными о сервере
     * @return int
     * @throws RestException
     */
    public static function add($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->add($arParams, $navStart, $server);
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
    public static function list($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->list($arParams, $navStart, $server);
    }

    /**
     * @param $arParams - поля сущности с значениями которые будут обновлены
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * @param \CRestServer $server - объект с данными о сервере
     * @return int
     * @throws RestException
     */
    public static function update($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->update($arParams, $navStart, $server);
    }

    /**
     * @param $arParams - обязательный ключ ID записи сущности
     * @param $navStart - если в ключе start перезать число то будет использован offset
     * * @param \CRestServer $server - объект с данными о сервере
     * @return bool
     * @throws RestException
     */
    public static function delete($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->delete($arParams, $navStart, $server);
    }

    /**
     * Метод возвращает сервис если такой зарегистрирован в сервис-локаторе
     * или выбрасывает исклчение если его там нет
     * @param string $code - код сервиса
     * @return mixed
     * @throws RestException
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     */
    private static function getService(string $code)
    {
        if (!ServiceLocator::getInstance()->has($code)) {
            throw new RestException(
                json_encode(Loc::getMessage('EXCEPTION_CAR_REST_METHOD_NOT_FOUND'), JSON_UNESCAPED_UNICODE),
                RestException::ERROR_METHOD_NOT_FOUND, \CRestServer::STATUS_NOT_FOUND
            );
        }

        return ServiceLocator::getInstance()->get($code);
    }
}