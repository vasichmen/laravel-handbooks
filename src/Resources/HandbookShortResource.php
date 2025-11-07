<?php

namespace Laravel\Handbooks\Resources;

use Laravel\Foundation\Abstracts\AbstractModel;
use Laravel\Foundation\Abstracts\AbstractResource;

class HandbookShortResource extends AbstractResource
{
    public function toArray($request)
    {
        /** @var AbstractModel|AbstractResource $this */
        return [...$this->attributesToArray(), ...$this->relationsToArray()];
    }
}
