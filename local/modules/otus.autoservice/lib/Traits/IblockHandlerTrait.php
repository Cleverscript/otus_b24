<?php
namespace Otus\Autoservice\Traits;

trait IblockHandlerTrait
{
    /**
     * Возвращает истину или ложь является ли инфоблок разрешенным
     * для выполнения хендлер-методов на обработчиках событий
     * используется для прерывания выполнения
     * @param $elementId
     * @param string $moduleId
     * @param int $iblockId
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function isAllowIblock($elementId = null, int $iblockId = 0): bool
    {
        if (!$iblockId) {
            $iblockId = \Otus\Autoservice\Services\IblockService::getIblockIdByElement($elementId);
        }

        return $iblockId == self::$entityIblockId;
    }
}