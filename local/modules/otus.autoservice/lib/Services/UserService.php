<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Engine\CurrentUser;

class UserService
{
    public function __construct()
    {

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
