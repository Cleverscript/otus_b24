<?php
/**
 * Регистрация класса как сервиса в ServiceLocator
 */
return [
    'services' => [
        'value' => [
            'otus.customrest_car.storage' => [
                'className' => '\\Otus\\Customrest\\Services\\CarStorageService',
            ]
        ],
        'readonly' => true,
    ]
];