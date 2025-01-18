<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\UserTable;
use Otus\Autoservice\Services\ModuleService;

class UserService
{
    public function __construct()
    {

    }

    /**
     * Возвращает подразделения пользователя
     *
     * @param int $id
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getUserDepartment(int $id): array
    {
        return UserTable::query()
            ->where('ID', $id)
            ->addSelect('UF_DEPARTMENT')
            ->fetch()['UF_DEPARTMENT'];
    }

    /**
     * Вернет истину если пользователь из подразделения "Механики"
     *
     * @param int $id
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function isMechanic(int $id): bool
    {
        return in_array(
            (int) ModuleService::getInstance()->getPropVal('OTUS_AUTOSERVICE_DEPARTMENT_MECHANIC'),
            self::getUserDepartment($id)
        );
    }

    /**
     * Возвращает ФИО пользователя
     *
     * @param int $id
     * @return string|null
     */
    public static function getFullName(int $id): ?string
    {
        if (!$id) {
            return null;
        }

        $rsUser = \CUser::GetByID($id);
        $arUser = $rsUser->Fetch();

        return implode(' ', [
            $arUser['NAME'],
            $arUser['SECOND_NAME'],
            $arUser['LAST_NAME']
        ]);
    }
}
