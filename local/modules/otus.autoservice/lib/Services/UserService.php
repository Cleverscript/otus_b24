<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Engine\CurrentUser;

class UserService
{
    public function __construct()
    {

    }

    public static function getFullName(int $id): ?string
    {
        if (!$id) {
            return null;
        }

       return CurrentUser::get()->getFullName();
    }
}