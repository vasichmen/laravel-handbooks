<?php


namespace Laravel\Handbooks\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Foundation\DTO\GetEntityRequestDTO;
use Laravel\Foundation\Presenters\DataResultPresenter;
use Laravel\Foundation\Presenters\PaginatedDataPresenter;
use Laravel\Handbooks\Requests\DeleteHandbookRequest;
use Laravel\Handbooks\Requests\GetHandbookDetailRequest;
use Laravel\Handbooks\Requests\GetHandbookDynamicListRequest;
use Laravel\Handbooks\Requests\GetHandbookDynamicShortListRequest;
use Laravel\Handbooks\Requests\GetHandbookDynamicShortPackListRequest;
use Laravel\Handbooks\Requests\GetHandbookListRequest;
use Laravel\Handbooks\Services\DynamicService;
use Laravel\Handbooks\Services\HandbookService;

class HandbookController extends Controller
{

    public function list(GetHandbookListRequest $request): PaginatedDataPresenter
    {
        $config = $this->getHandbookConfig();
        $resource = $config['resource'];
        return new PaginatedDataPresenter(HandbookService::list($request->validated(), $config), resourceNamespace: $resource);
    }


    public function dynamicList(string $model, string $field, GetHandbookDynamicListRequest $request): DataResultPresenter
    {
        return new DataResultPresenter([
            'data' => DynamicService::getDynamicList($model, $field, $request->validated())
        ]);
    }

    public function dynamicShortList(string $model, GetHandbookDynamicShortListRequest $request): DataResultPresenter
    {
        return new DataResultPresenter([
            'data' => DynamicService::getDynamicShortList($model, $request->validated())
        ]);
    }

    public function dynamicShortPackList(GetHandbookDynamicShortPackListRequest $request): DataResultPresenter
    {
        return new DataResultPresenter([
            'data' => DynamicService::getDynamicShortPackList($request->validated())
        ]);
    }

    public function detail(GetHandbookDetailRequest $request): DataResultPresenter
    {
        /** @var GetEntityRequestDTO $params */
        $params = $request->validated();
        $config = $this->getHandbookConfig();
        $resource = $config['resource'];
        return new DataResultPresenter([
            'item' => new $resource(HandbookService::detail($params, $config)),
        ]);
    }


    public function create(): DataResultPresenter
    {
        $config = $this->getHandbookConfig();
        $resource = $config['resource'];
        $request = app($config['create_request']);

        return new DataResultPresenter([
            'item' => new $resource(HandbookService::create($request->validated(), $config))
        ]);
    }

    public function update(string $handbookId): DataResultPresenter
    {
        $config = $this->getHandbookConfig();
        $resource = $config['resource'];
        $request = app($config['update_request']);

        return new DataResultPresenter([
            'item' => new $resource(HandbookService::update($request->validated(), $handbookId, $config))
        ]);
    }

    public function delete(DeleteHandbookRequest $request): DataResultPresenter
    {
        return new DataResultPresenter(
            HandbookService::delete($request->validated()->id, $this->getHandbookConfig())
        );
    }

    private function getHandbookConfig()
    {
        $route = app('router')->getCurrentRoute()->getName();
        $code = Str::after(Str::beforeLast($route, '.'), '.');
        return config("handbooks.crud.$code");
    }

}
