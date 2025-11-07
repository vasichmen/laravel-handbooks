<?php


namespace Laravel\Handbooks\Exceptions;

use Laravel\Foundation\Abstracts\AbstractApiException;

class HandbookCreateException extends AbstractApiException
{
    public const EXCEPTION_CODE = 500;
    public const EXCEPTION_NAME = 'handbook_create_error';
}
