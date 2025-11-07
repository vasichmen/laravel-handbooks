# Подключение

* установить пакет

```shell
 composer require ippu/handbooks
```

* подключить `\IPPU\Handbooks\IppuHandbooksServiceProvider::class` в `config\app.php`
* опубликовать конфиг
```shell
php artisan vendor:publish --provider="IPPU\Handbooks\IppuHandbooksServiceProvider"
```
* заполнить заготовку конфига `handbooks.php`. 
    
* Пример конфига:
```php
[
    'route_prefix' => env('HANDBOOKS_ROUTE_PREFIX', ''),
    'global_middleware' => ['user-auth', 'local-auth'],
    'defaults' => [
        'middleware' => [
            'create' => [PermissionCodeEnum::ManageHandbooks->middleware()], //мидлвары для создания по умолчанию, например can:manage_handbooks
            'update' => [PermissionCodeEnum::ManageHandbooks->middleware()], //мидлвары для обновления по умолчанию, например can:manage_handbooks
            'delete' => [PermissionCodeEnum::ManageHandbooks->middleware()], //мидлвары для удаления по умолчанию, например can:manage_handbooks
            'view' => [PermissionCodeEnum::ViewHandbooks->middleware()], //мидлвары для просмотра по умолчанию, например can:manage_handbooks
        ],
    ],
    'dynamic' => [
        'model_base_namespace' => '\\App\\Models\\',
        'repository_base_namespace' => '\\App\\Repositories\\',
        'default_select' => ['id', 'name'],
        'default_with' => [],
        'default_searchable_fields' => ['name'],
        'custom' => [
            //модели, которые находятся в других пространствах имен или нужны кастомные настройки
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
        'functional-area' => [
            'repository' => \App\Repositories\FunctionalAreaRepository::class,
            'resource' => \App\Http\Resources\FunctionalAreaResource::class,
            'create_request' => \App\Http\Requests\Handbook\Create\CreateFunctionalAreaRequest::class,
            'update_request' => \App\Http\Requests\Handbook\Update\UpdateFunctionalAreaRequest::class,
            'create_dto' => \App\DTO\Request\Handbook\FunctionalAreaHandbookDTO::class,
            'update_dto' => \App\DTO\Request\Handbook\FunctionalAreaHandbookDTO::class,
            'searchable_fields'=>['name'],
            'with'=>['detail'=>['system', 'architectManagers'],'list'=>['system']]
        ]
    ],
    'enums'=>[
        ComponentTypeEnum::class,
        MicroserviceCodeEnum::class,
    ]   
]
```

* Пример реквеста создания:

```php
class CreateFunctionalAreaRequest extends AbstractRequest
{
    protected ?string $dtoClassName = FunctionalAreaHandbookDTO::class;

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', new Unique(FunctionalArea::class, 'name')],
            'architect_managers' => ['array', 'sometimes'],
            'architect_managers.*' => ['uuid', new Exists(User::class, 'id')],
            'corp_architects' => ['array', 'sometimes'],
            'corp_architects.*' => ['uuid', new Exists(User::class, 'id')],
        ];
    }
}
```

* Пример реквеста обновления:

```php
class UpdateFunctionalAreaRequest extends CreateFunctionalAreaRequest
{
    protected ?string $dtoClassName = FunctionalAreaHandbookDTO::class;

    public function rules()
    {
        return [
            ...parent::rules(),
            'name' => [
                'sometimes',
                'string',
                'max:255',
                (new Unique(FunctionalArea::class, 'name'))->ignore($this->getRouteParameter('handbookId'))
            ],
        ];
    }
}
```
* Если в проекте используются кастомные связи BelongsToMany, то такие связи могут реализовать интерфейс [ProvidesUniqueRelatedKeys](src/ProvidesUniqueRelatedKeys.php). 
Пример такой реализации: https://git.ds.ecpk.sibintek.ru/project/ippu/dev/ukita-service/-/blob/master/app/Models/Relations/EntityArchitectsRelation.php?ref_type=heads
