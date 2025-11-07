<?php

namespace Laravel\Handbooks\Services;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Foundation\Abstracts\AbstractDto;
use Laravel\Foundation\Abstracts\AbstractModel;
use Laravel\Foundation\Abstracts\AbstractRepository;
use Laravel\Foundation\Abstracts\AbstractService;
use Laravel\Foundation\DTO\GetEntityRequestDTO;
use Laravel\Foundation\DTO\GetListRequestDTO;
use Laravel\Handbooks\Exceptions\HandbookCreateException;
use Laravel\Handbooks\Exceptions\HandbookNotFoundException;
use Laravel\Handbooks\Exceptions\KeyViolationException;

class HandbookService extends AbstractService
{
    public static function list(GetListRequestDTO $params, array $config): LengthAwarePaginator
    {
        /** @var AbstractRepository $repositoryNamespace */
        $repositoryNamespace = $config['repository'];

        $with = $config['with']['list'] ?? $repositoryNamespace::newQuery()->getModel()::getDefinedRelations();
        $searchableFields = $config['searchable_fields'] ?? [];

        return $repositoryNamespace::query()
            ->fromGetListDto($params, $searchableFields)
            ->with($with)
            ->paginate();
    }


    public static function detail(GetEntityRequestDTO $params, array $config): AbstractModel
    {
        $repositoryNamespace = $config['repository'];
        $model = $repositoryNamespace::getModel($params->id);

        if (empty($model)) {
            throw new HandbookNotFoundException();
        }

        $model->load($config['with']['detail'] ?? $model::getDefinedRelations());
        return $model;
    }

    public static function create(array|AbstractDto $params, array $config): AbstractModel
    {
        $repositoryNamespace = $config['repository'];
        try {
            DB::beginTransaction();

            $model = $repositoryNamespace::create($params);
            if (empty($model)) {
                throw new HandbookCreateException();
            }

            self::bindBelongsToManyRelations($model, $params);

            DB::commit();

            return self::detail(new GetEntityRequestDTO(['id' => $model->id,]), $config);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update(array|AbstractDto $params, string $handbookId, array $config): AbstractModel
    {
        $repositoryNamespace = $config['repository'];
        try {
            DB::beginTransaction();

            $model = $repositoryNamespace::update($handbookId, $params);
            if (empty($model)) {
                throw new HandbookNotFoundException();
            }

            self::bindBelongsToManyRelations($model, $params);

            DB::commit();

            return self::detail(new GetEntityRequestDTO(['id' => $model->id,]), $config);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete(string $id, array $config): bool
    {
        $repositoryNamespace = $config['repository'];
        try {
            $result = $repositoryNamespace::delete($id);
            if (empty($result)) {
                throw new HandbookNotFoundException();
            }
            return $result;
        } catch (Exception $e) {
            //если ошибка "foreign key violation"
            if ($e->getCode() == 23503) {
                throw new KeyViolationException('Удаляемая запись связана с другими сущностями, которые не могут быть удалены автоматически. Воспользуйтесь деактивацией или обратитесь к администратору');
            }
            throw $e;
        }
    }

    private static function bindBelongsToManyRelations(AbstractModel $model, AbstractDto|array $params): void
    {
        if ($params instanceof AbstractDto) {
            $params = $params->toArray();
        }

        //запись связей многие ко многим
        $relations = $model::getDefinedRelations(BelongsToMany::class);
        foreach ($params as $paramName => $paramValue) {
            $relation = Str::camel($paramName);
            if (in_array($relation, $relations)) {
                $model->{$relation}()->sync($paramValue);
            }
        }
    }
}
