<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Loader;
use Bitrix\Crm\ContactTable;

class ContactService
{
    public function __construct()
    {

    }

    /**
     * Возвращает ФИО контакта
     *
     * @param int $id
     * @return string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFullName(int $id): ?string
    {
        if (!$id) {
            return null;
        }

        return ContactTable::query()
            ->where('ID', $id)
            ->addSelect('FULL_NAME')
            ->fetch()['FULL_NAME'];
    }
}
