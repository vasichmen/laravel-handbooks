<?php


namespace Laravel\Handbooks\Exceptions;

use Laravel\Foundation\Abstracts\AbstractApiException;

class HandbookDtoNotFoundException extends AbstractApiException
{
    public const EXCEPTION_CODE = 500;
    public const EXCEPTION_NAME = 'handbook_dto_not_found';
}
