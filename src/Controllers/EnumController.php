<?php


namespace Laravel\Handbooks\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Foundation\Presenters\DataResultPresenter;
use Laravel\Foundation\Traits\Enum\BaseEnumTrait;
use Laravel\Handbooks\Services\EnumService;

class EnumController extends Controller
{
    public function list(): DataResultPresenter
    {
        /** @var \UnitEnum|BaseEnumTrait|string $class */
        $class = Str::after(Route::currentRouteName(), 'handbook.enum.');
        return new DataResultPresenter(['data' => EnumService::list($class)]);
    }

}
