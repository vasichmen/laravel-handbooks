<?php

namespace Laravel\Handbooks\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Laravel\Foundation\Abstracts\AbstractService;

class EnumService extends AbstractService
{
    public static function list($class): Collection
    {
        //если есть такой ключ массива в конфиге, значит там записан конфиг. Надо его применить для каждого элемента.
        //если ключа нет, значит отдаем весь список значений енума
        if (array_key_exists($class, config("handbooks.enums", []))) {
            $filter = config("handbooks.enums.$class")['filter'] ?? null;
            return collect($class::cases())
                ->filter(static fn($item) => $filter ? $filter($item) : true)
                ->map(static fn($item) => $item->render())
                ->values();
        } else {
            return $class::list();
        }
    }

    public static function getEnumClass(string $code): false|string
    {
        $routes = Route::getRoutes();
        $code = \Str::kebab($code);
        foreach ($routes as $route) {
            /** @var \Illuminate\Routing\Route $route */
            if (\Str::endsWith($route->uri(), "enum/$code")) {
                return \Str::after($route->getName(), 'handbook.enum.');
            }
        }
        return false;
    }
}