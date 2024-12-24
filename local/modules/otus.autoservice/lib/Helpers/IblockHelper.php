<?php

namespace Otus\Autoservice\Helpers;

use Bitrix\Main\Config\Option;
use Bitrix\Iblock\ElementTable;

/**
 * Класс с хелпер-методами для инфоблока
 */
class IblockHelper
{
    /**
     * Возвращает ID инфоблока эл-нта
     * @param int $elementId
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIblockIdByElement(int $elementId)
    {
        return ElementTable::getList([
            'select' => [
                'IBLOCK_ID',
            ],
            'filter' => ['ID' => $elementId],
        ])->fetch()['IBLOCK_ID'];
    }

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
    public static function isAllowIblock($elementId = null, string $moduleId, int $iblockId = 0): bool
    {
        if (!$iblockId) {
            $iblockId = self::getIblockIdByElement($elementId);
        }

        return $iblockId == Option::get($moduleId, 'OTUS_AUTOSERVICE_IB_CARS');
    }
}