<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Handbooks\Controllers\EnumController;
use Laravel\Handbooks\Controllers\HandbookController;

//название api префикса
$apiPrefix = config('handbooks.api_prefix') ?? 'api/v1';

//название сервиса
$serviceName = config('app.service_name') ?? (app()->runningInConsole() ? '' : throw new \Exception('Не заполнен параметр SERVICE_NAME в .env или нет ключа service_name в config/app.php'));

//префикс менеджера файлов (если есть)
$prefix = config('handbooks.route_prefix');
if (!empty($prefix)) {
    $prefix = '/' . $prefix;
}

Route::prefix("$apiPrefix/$serviceName$prefix/enum")
    ->middleware(config('handbooks.global_middleware', []))
    ->group(function () {
        foreach (config('handbooks.enums', []) as $class => $params) {
            //массив может содержать список енумов, а может в ключах хранить классы енумов, а в значениях - фильтр-функции
            $class = match (true) {
                is_array($params) => $class,
                is_numeric($class) => $params,
            };
            $prefix = Str::kebab(Str::before(Str::afterLast($class, '\\'), 'Enum'));
            Route::get($prefix, [EnumController::class, 'list'])->middleware(config('handbooks.enum_middleware', []))->name("handbook.enum.$class");
        }
    });

Route::prefix("$apiPrefix/$serviceName$prefix/handbook")
    ->middleware(config('handbooks.global_middleware', []))
    ->group(function () {
        Route::get('/dynamic/{model}/{field}', [HandbookController::class, 'dynamicList']);
        Route::get('/short/{model}', [HandbookController::class, 'dynamicShortList']);
        Route::post('/short-pack', [HandbookController::class, 'dynamicShortPackList']);

        if (empty(config('handbooks.crud'))) {
            return;
        }

        foreach (config('handbooks.crud') as $prefix => $params) {
            Route::prefix($prefix)
                ->middleware($params['view_middleware'] ?? config('handbooks.defaults.middleware.view'))
                ->group(function () use ($prefix) {
                    Route::get('/', [HandbookController::class, 'list'])->name("handbooks.$prefix.list");

                    Route::middleware($params['create_middleware'] ?? config('handbooks.defaults.middleware.create'))
                        ->post('/', [HandbookController::class, 'create'])->name("handbooks.$prefix.create");;

                    Route::prefix('{handbookId}')
                        ->group(function () use ($prefix) {
                            Route::get('/', [HandbookController::class, 'detail'])->name("handbooks.$prefix.detail");;

                            Route::middleware($params['update_middleware'] ?? config('handbooks.defaults.middleware.update'))
                                ->post('/', [HandbookController::class, 'update'])
                                ->name("handbooks.$prefix.update");
                            Route::middleware($params['delete_middleware'] ?? config('handbooks.defaults.middleware.delete'))
                                ->delete('/', [HandbookController::class, 'delete'])
                                ->name("handbooks.$prefix.delete");
                        });
                });
        }
    });
