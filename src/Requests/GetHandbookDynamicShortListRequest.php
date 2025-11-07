<?php

namespace Laravel\Handbooks\Requests;

use Laravel\Foundation\Abstracts\AbstractRequest;
use Laravel\Foundation\DTO\GetListRequestDTO;

class GetHandbookDynamicShortListRequest extends AbstractRequest
{
    protected ?string $dtoClassName = GetListRequestDTO::class;

    public function rules()
    {
        return [
            'q' => 'sometimes|string',
            'filters' => ['array', 'sometimes'],
            'queries' => ['array', 'sometimes'],

            ...$this->sorted(),
        ];
    }
}
