<?php

namespace Laravel\Handbooks\DTO;

use Illuminate\Support\Collection;
use Laravel\Foundation\Abstracts\AbstractDto;

class GetShortListPackRequestDTO extends AbstractDto
{
    /**
     * @var Collection|array{array{params:array,model:string}}
     */
    public Collection $items;
}