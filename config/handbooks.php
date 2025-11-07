<?php
return [
    'route_prefix' => env('HANDBOOKS_ROUTE_PREFIX', ''),
    'api_prefix' => env('HANDBOOKS_API_PREFIX', 'api/v1'),
    'defaults' => [
        'middleware' => [
            'global' => [],//глобальные мидлвары на всю группу handbooks
            'create' => [], //мидлвары для создания по умолчанию, например can:manage_handbooks
            'update' => [], //мидлвары для обновления по умолчанию, например can:manage_handbooks
            'delete' => [], //мидлвары для удаления по умолчанию, например can:manage_handbooks
            'view' => [], //мидлвары для просмотра по умолчанию, например can:manage_handbooks
        ],
    ],
    'dynamic' => [
        //todo заполнить настройки сущностей, которые подключаются только к методам short и dynamic
        'model_base_namespace' => '\\App\\Models\\',
        'repository_base_namespace' => '\\App\\Repositories\\',
        'default_select' => ['id', 'name'],
        'default_with' => [],
        'default_searchable_fields' => ['name'],
        'custom' => [
            //модели, которые находятся в других пространствах имен
//            'model-code' => [
//                'model' => ModelClass::class,
//                'resource' => ModelResourceClass::class,
//                'repository' => ModelRepositoryClass::class,
//                'select' => ['id','name'],//массив полей, которые надо доставать для short-лист. По умолчанию id,name
//                'with' => [], //массив отношений, которые надо доставать для short-лист. По умолчанию пустой
//                'searchable_fields' => [], //массив полей, по которым возможен поиск
//            ],
        ]
    ],
    'crud' => [
        //todo заполнить настройки сущностей, с которыми можно проводить операции редактирования
        //kebab код справочника для роутера => [
        //  'repository' => класс репозитория,
        //  'resource'=> класс ресурса,
        //  'create_request'=> класс реквеста создания сущности. Обязательно должен подключать DTO
        //  'update_request'=> класс реквеста обновления сущности. Обязательно должен подключать DTO
        //  'create_middleware'=> ['мидлвары для создания, например can:manage_handbooks. По умолчанию используется defaults.middleware.create']
        //  'delete_middleware'=> ['мидлвары для удаления, например can:manage_handbooks. По умолчанию используется defaults.middleware.delete']
        //  'update_middleware'=> ['мидлвары для изменения, например can:manage_handbooks. По умолчанию используется defaults.middleware.update']
        //  'view_middleware'=> ['мидлвары для просмотра, например can:manage_handbooks. По умолчанию используется defaults.middleware.view']
        //  'searchable_fields'=>[], //массив полей, по которым возможен поиск
        //  'with' => [// массив связей, загружаемых методами update,create,detail,list. По умолчанию загружаются все связи модели
        //          'detail'=>[], //методы update, create, detail
        //          'list'=>[], //метод list
        //      ]
        //]
    ],
    'enums'=>[
        //todo перечислить классы енумов
        //EnumClass::class
    ]

];