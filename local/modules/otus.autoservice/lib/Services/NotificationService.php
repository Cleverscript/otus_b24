<?php
namespace Otus\Autoservice\Services;

use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Traits\ModuleTrait;

Loc::loadMessages(__FILE__);

class NotificationService
{
    use ModuleTrait;

    public function __construct()
    {

    }

    /**
     * Отправляет уведомление в ЛС пользователю
     *
     * @param int $creatorId
     * @param int $assignedId
     * @param string $mess
     * @return void
     */
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
}
