<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Loader;
use Bitrix\Crm\ContactTable;

class ContactService
{
    public function __construct()
    {
        $this->includeModules();
    }

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

    private function includeModules(): void
    {
        if (!Loader::includeModule('crm')) {
            throw new \Exception(Loc::getMessage(
                "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
                ['#MODULE_ID#' => 'crm']
            ));
        }
    }
}