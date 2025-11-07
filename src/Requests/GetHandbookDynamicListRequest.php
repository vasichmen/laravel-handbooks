<?php


namespace Laravel\Handbooks\Requests;

use Laravel\Foundation\Abstracts\AbstractRequest;
use Laravel\Foundation\DTO\GetListRequestDTO;

class GetHandbookDynamicListRequest extends AbstractRequest
{
    protected ?string $dtoClassName = GetListRequestDTO::class;

    public function rules()
    {
        return [
            'filters' => ['array', 'sometimes'],
        ];
    }
}
