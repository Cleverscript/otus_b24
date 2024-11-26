<?php

namespace Otus\Customrest\Handlers;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\DI\ServiceLocator;

Loc::loadMessages(__FILE__);

class CarRestHandler
{
    protected static $service = 'otus.customrest.car.storage';

    public static function carRestMethodsRegistration()
    {
        Loc::getMessage('REST_SCOPE_OTUS.CUSTOMREST_CAR');

        return [
            'otus.customrest.car' => [
                'otus.customrest.car.add' => [__CLASS__, 'add'],
                'otus.customrest.car.list' => [__CLASS__, 'list'],
                'otus.customrest.car.update' => [__CLASS__, 'update'],
                'otus.customrest.car.delete' => [__CLASS__, 'delete'],
            ]
        ];
    }

    public static function add($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->add($arParams, $navStart, $server);
    }

    public static function list($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->list($arParams, $navStart, $server);
    }

    public static function update($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->update($arParams, $navStart, $server);
    }

    public static function delete($arParams, $navStart, \CRestServer $server)
    {
        $service = self::getService(self::$service);

        return $service->delete($arParams, $navStart, $server);
    }

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