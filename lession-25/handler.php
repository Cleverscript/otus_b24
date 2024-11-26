<?php
require_once __DIR__ . '/crest.php';

/**
 * Handler принимает данные от входящего web-hook
 * который выполняет запрос при событии создания дела для контакта
 * и написании комментария в таймлайне какрточки контакта.
 * В из данных определяем ID контакта и DATETIME созданного комментария или дела,
 * затем проверяем есть ли у контакта поле с определнным XML_ID, и если нет, то
 * создаем это поле и записываем в него DATETIME комментария или дела, если поле
 * уже есть, то просто пишем в него.
 */

try {
    CRest::setLog([$_REQUEST, $_SERVER], 'debug');

    if ($_REQUEST['auth']['application_token'] != C_REST_WEB_HOOK_TOKEN) {
        CRest::setLog($_REQUEST, 'invalid_token');
        die('Invalid token');
    }

    $data = $_REQUEST['data'];
    $event = $_REQUEST['event'];

    switch ($event) {
        case 'ONCRMACTIVITYADD': {
            $activityId = intval($data['FIELDS']['ID']);

            if (!$activityId) {
                throw new \InvalidArgumentException('Invalid activity ID');
            }

            $result = CRest::call(
                'crm.activity.get',
                ['ID' => $activityId]
            );

            CRest::setLog($result, 'activity');

            $contactId = intval($result['result']['OWNER_ID']);

            $datetime = date('Y-m-d H:i:s', strtotime($result['result']['CREATED']));

            break;
        }
        case 'ONCRMTIMELINECOMMENTADD': {
            $commentId = intval($data['FIELDS']['ID']);

            if (!$commentId) {
                throw new \InvalidArgumentException('Invalid comment ID');
            }

            $result = CRest::call(
                'crm.timeline.comment.get',
                ['ID' => $commentId]
            );

            CRest::setLog($result, 'comment');

            $contactId = intval($result['result']['ENTITY_ID']);

            $datetime = date('Y-m-d H:i:s', strtotime($result['result']['CREATED']));

            break;
        }
        case 'ONIMCONNECTORMESSAGEADD': {
            // Для открытые линии
        }
        default: {
            throw new \RuntimeException('Unknown event');
        }
    }

    if (!$contactId) {
        throw new \InvalidArgumentException('Invalid contact ID');
    }

    if (!$datetime) {
        throw new \InvalidArgumentException('Invalid datetime');
    }

    $result = CRest::call('crm.contact.userfield.list', [
        'order' => ["SORT" => "ASC"],
        'FILTER' => [
            'XML_ID' => C_REST_CONTACT_FIELD_XML_ID
        ]
    ]);

    CRest::setLog($result, 'userfield_list');

    if (empty($result['result'])) {
        $result = CRest::call('crm.contact.userfield.add', [
            'FIELDS' => [
                'XML_ID' => C_REST_CONTACT_FIELD_XML_ID,
                'FIELD_NAME' => 'UF_CRM_COMMUNICATION_LAST_DATETIME',
                'EDIT_FORM_LABEL' => 'Дата последней коммуникации',
                'LIST_COLUMN_LABEL' => 'Дата последней коммуникации',
                'USER_TYPE_ID' => 'datetime',
                'SETTINGS' => [ 'DEFAULT_VALUE' => '00.00.0000 00:00:00' ]
            ]
        ]);

        CRest::setLog($result, 'userfield_add');
    }

    $result = CRest::call(
        'crm.contact.update',
        [
            'ID' => $contactId,
            'FIELDS' => [
                C_REST_CONTACT_FIELD_XML_ID => $datetime,
            ],
            'PARAMS' => ['REGISTER_SONET_EVENT' => 'Y']
        ]
    );

    CRest::setLog(
        array_merge(
            $result,
            ['FIELDS' => [C_REST_CONTACT_FIELD_XML_ID => $datetime]]
        ),
        'contact_update'
    );

} catch (\Throwable $e) {
    CRest::setLog([$e->getMessage() => $e->getTrace()], 'EXCEPTION');
}