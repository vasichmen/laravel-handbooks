<?php


namespace Laravel\Handbooks\Exceptions;

use Laravel\Foundation\Abstracts\AbstractApiException;

class HandbookNotFoundException extends AbstractApiException
{
    public const EXCEPTION_CODE = 404;
    public const EXCEPTION_NAME = 'handbook_not_found';
}
