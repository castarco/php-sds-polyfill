<?php
declare(strict_types=1);


namespace SDS\Exceptions;


class ShapeMismatchException extends \LogicException implements SDSException
{
    public function __construct($message='The tensor shapes don\'t match', $code=0, \Exception $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}
