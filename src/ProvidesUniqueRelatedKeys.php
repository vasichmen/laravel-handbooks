<?php

namespace Laravel\Handbooks;

use Illuminate\Database\Query\Builder;


interface ProvidesUniqueRelatedKeys
{
    /**Возвращает список id элементов связей
     * @return Builder
     */
    public function getUniqueRelatedKeysQuery(): Builder;
}