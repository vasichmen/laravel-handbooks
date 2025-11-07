<?php

namespace Laravel\Handbooks\Requests;

use Laravel\Foundation\Abstracts\AbstractRequest;
use Laravel\Handbooks\DTO\GetShortListPackRequestDTO;

class GetHandbookDynamicShortPackListRequest extends AbstractRequest
{
    protected ?string $dtoClassName = GetShortListPackRequestDTO::class;

    public function rules()
    {
        return [
            'items' => 'array',
            'items.*' => 'array',
            'items.*.model' => "required|string",
            'items.*.key' => "required|string|distinct",
            'items.*.params' => "string|nullable",
        ];
    }
}
