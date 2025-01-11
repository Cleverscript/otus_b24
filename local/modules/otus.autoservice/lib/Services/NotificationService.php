<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class NotificationService
{
    use ModuleTrait;

    public function __construct()
    {
        $this->includeModules();
    }

    public function sendNotification(int $creatorId, int $assignedId, string $mess): void
    {
        $fields = [
            "FROM_USER_ID" => $creatorId,
            "TO_USER_ID" => $assignedId,
            "NOTIFY_TYPE" => 4,
            "NOTIFY_MODULE" => self::$moduleId,
            "NOTIFY_TAG" => "",
            "NOTIFY_MESSAGE" => $mess,
        ];

        \CIMNotify::Add($fields);
    }

    /**
     * Подключает модули
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    private function includeModules(): void
    {
        if (!Loader::includeModule('im')) {
            throw new \Exception(Loc::getMessage(
                "OTUS_AUTOSERVICE_MODULE_IS_NOT_INSTALLED",
                ['#MODULE_ID#' => 'im']
            ));
        }
    }
}