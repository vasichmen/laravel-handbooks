<?php


namespace Laravel\Handbooks\Exceptions;

use Laravel\Foundation\Abstracts\AbstractApiException;

class KeyViolationException extends AbstractApiException
{
    public const EXCEPTION_CODE = 400;
    public const EXCEPTION_NAME = 'key_violation';
}
