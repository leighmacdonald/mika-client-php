<?php


namespace Mika;


use Throwable;

class BadRequest extends MikaException
{
    function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}