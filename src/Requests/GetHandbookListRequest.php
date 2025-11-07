<?php

namespace Laravel\Handbooks\Requests;


use Laravel\Foundation\Abstracts\AbstractRequest;
use Laravel\Foundation\DTO\GetListRequestDTO;

class GetHandbookListRequest extends AbstractRequest
{
    protected ?string $dtoClassName = GetListRequestDTO::class;

    public function rules()
    {
        return [
            'q' => 'sometimes|string',
            'filters' => 'sometimes|array',

            ...$this->paginated(),
            ...$this->sorted(),
        ];
    }
}
