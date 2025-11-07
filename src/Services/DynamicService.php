<?php

namespace Laravel\Handbooks\Services;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Foundation\Abstracts\AbstractModel;
use Laravel\Foundation\Abstracts\AbstractRepository;
use Laravel\Foundation\Abstracts\AbstractResource;
use Laravel\Foundation\Abstracts\AbstractService;
use Laravel\Foundation\DTO\GetListRequestDTO;
use Laravel\Handbooks\DTO\GetShortListPackRequestDTO;
use Laravel\Handbooks\ProvidesUniqueRelatedKeys;
use Laravel\Handbooks\Resources\HandbookShortResource;

class DynamicService extends AbstractService
{
    public static function getDynamicShortList(string $model, GetListRequestDTO $params): AnonymousResourceCollection
    {
        [, $repositoryNamespace, $resourceNamespace, $with, $queryable, $select] = self::getDynamicParams($model);

        $builder = $repositoryNamespace::query()
            ->select($select)
            ->with($with)
            ->filters($repositoryNamespace::getSupportingFilters($params->filters));

        if (!empty($params->q)) {
            $builder->query($params->q, $queryable);
        }

        $items = $builder->queries($params->queries)
            ->orderBy($params->sort)
            ->get();

        return $resourceNamespace::collection($items);
    }

    public static function getDynamicShortPackList(GetShortListPackRequestDTO $params): Collection
    {
        $results = collect();
        foreach ($params->items as $item) {
            parse_str($item['params'], $query);
            $query['sort'] = $query['sort'] ?? ['id' => 'asc'];

            $enumClass = EnumService::getEnumClass($item['model']);
            $items = match (true) {
                //енумы
                !empty($enumClass) => EnumService::list($enumClass),
                //динамический шорт лист
                Str::contains($item['model'], '.') => self::getDynamicList(
                    Str::before($item['model'], '.'),
                    Str::after($item['model'], '.'),
                    new GetListRequestDTO([...Arr::only($query, ['q', 'filters', 'queries'])])
                ),
                //обычный шорт лист
                default => self::getDynamicShortList(
                    $item['model'],
                    new GetListRequestDTO([...Arr::only($query, ['q', 'filters', 'queries', 'sort'])])
                )
            };

            $results[$item['key']] = ['data' => $items];
        }
        return $results;
    }

    public static function getDynamicList(string $modelName, string $field, GetListRequestDTO $params): Collection
    {
        [$modelNamespace, $repositoryNamespace, , , $queryable] = self::getDynamicParams($modelName);

        $builder = $repositoryNamespace::query()
            ->filters($params->filters)
            ->queries($params->queries);

        if (!empty($params->q)) {
            $builder->query($params->q, $queryable);
        }

        $query = $builder->toQuery();

        $model = new $modelNamespace();
        $belongsToRelations = $model::getDefinedRelations(BelongsTo::class);
        $belongsToManyRelations = $model::getDefinedRelations(BelongsToMany::class);
        $fields = $model->getFillable();
        $casts = $model->getCasts();

        switch (true) {
            //поле модели
            case in_array($fieldName = Str::snake(Str::camel($field)), $fields):
                $items = $query->select([$fieldName]);
                //если это поле - массив строк в json
                if (array_key_exists($fieldName, $casts) && ($casts[$fieldName] == 'array' || $casts[$fieldName] == 'collection')) {
                    $items = $items
                        ->get()
                        ->pluck($fieldName)
                        ->collapse()
                        ->unique();
                } else {
                    //если это простое поле
                    $items = $items
                        ->distinct()
                        ->get()
                        ->pluck($fieldName);
                }

                $result = $items
                    ->filter()
                    ->values()
                    ->map(static fn($name) => ['name' => $name, 'id' => $name]);
                break;
            //одиночная привязка
            case in_array($relationName = Str::camel($field), $belongsToRelations):
                /** @var BelongsTo $relation */
                $relation = $model->{$relationName}();
                /** @var AbstractModel $relatedModel */
                $foreignKey = $relation->getForeignKeyName();
                $keys = $query->select([$foreignKey])->distinct()->get()->pluck($foreignKey);
                $result = $relation
                    ->getModel()
                    ->whereIn('id', $keys)
                    ->get()
                    ->filter()
                    ->values()
                    ->map(static fn(AbstractModel $item) => ['id' => $item->id, 'name' => $item->name]);
                break;
            //множественная привязка
            case in_array($relationName = Str::camel($field), $belongsToManyRelations):
                $relation = $model->{$relationName}();

                //подзапрос к модели для получения всех id записей по фильтрам
                $modelKeysQuery = $query->select([$model->getKeyName()])->toRawSql();

                //если используется кастомная связь, то она должна реализовать интерфейс ProvidesUniqueRelatedKeys, если у нее есть дополнительные условия
                $keysQuery = match (true) {
                    $relation instanceof ProvidesUniqueRelatedKeys => $relation->getUniqueRelatedKeysQuery(),
                    $relation instanceof BelongsToMany => DB::query(),
                    default => throw new Exception('Этот тип связи не реализован'),
                };

                //достаем id записей связанной модели с учетом фильтров на родительскую модель
                $keys = $keysQuery
                    ->from($relation->getTable())
                    ->select($relation->getRelatedPivotKeyName())
                    ->whereRaw($relation->getForeignPivotKeyName() . ' in (' . $modelKeysQuery . ')')
                    ->get()
                    ->pluck($relation->getRelatedPivotKeyName())
                    ->unique();

                $relatedItems = $relation->getRelated()->whereIn('id', $keys)->get();
                $result = $relatedItems->map(static fn(AbstractModel $item) => ['id' => $item->id, 'name' => $item->name]);
                break;
            default:
                throw new Exception("Поле или связь $field не найдена в модели $modelName. Или тип связи не реализован");
        }
        return $result->sortBy('name')->values();
    }


    /**Возвращает параметры из конфига по заданному названию модели
     * @param string $model
     * @return array{AbstractModel|string,AbstractRepository|string,AbstractResource|string,string[],string[],string[]}
     */
    private static function getDynamicParams(string $model): array
    {
        /** @var AbstractModel|string $modelNamespace */
        $modelNamespace = config(
            "handbooks.dynamic.custom.$model.model",
            config('handbooks.dynamic.model_base_namespace') . Str::ucfirst(Str::camel($model))
        );
        if (!class_exists($modelNamespace)) {
            throw new \Exception("Класс $modelNamespace не существует");
        }

        /** @var AbstractRepository|string $repositoryNamespace */
        $repositoryNamespace = config(
            "handbooks.dynamic.custom.$model.repository",
            config('handbooks.dynamic.repository_base_namespace') . Str::ucfirst(Str::camel($model)) . 'Repository'
        );
        if (!class_exists($repositoryNamespace)) {
            throw new \Exception("Класс $repositoryNamespace не существует");
        }

        /** @var AbstractResource|string $resourceNamespace */
        $resourceNamespace = config('handbooks.dynamic.custom.' . $model . '.resource', HandbookShortResource::class);
        if (!class_exists($resourceNamespace)) {
            throw new \Exception("Класс $resourceNamespace не существует");
        }

        $select = config("handbooks.dynamic.custom.$model.select", config("handbooks.dynamic.default_select"));
        $with = config("handbooks.dynamic.custom.$model.with", config("handbooks.dynamic.default_with"));
        $queryable = config("handbooks.dynamic.custom.$model.searchable_fields", config('handbooks.dynamic.default_searchable_fields'));

        return [$modelNamespace, $repositoryNamespace, $resourceNamespace, $with, $queryable, $select];
    }
}
