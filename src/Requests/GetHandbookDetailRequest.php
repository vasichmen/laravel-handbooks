<?php


namespace Laravel\Handbooks\Requests;

use Laravel\Foundation\Abstracts\AbstractRequest;
use Laravel\Foundation\DTO\GetEntityRequestDTO;

class GetHandbookDetailRequest extends AbstractRequest
{
    public function all($keys = null)
    {
        $params = parent::all();
        $params['id']=$this->getRouteParameter('handbookId');
        return $params;
    }

    protected ?string $dtoClassName = GetEntityRequestDTO::class;

    public function rules()
    {
        return [
            'id' => 'required|uuid',
        ];
    }
}
