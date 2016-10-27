<?php
declare(strict_types=1);


namespace SDS\Exceptions;


class InvalidPermutationException extends \InvalidArgumentException implements SDSException
{
    public function __construct($message='The passed array is not a valid permutation', $code=0, \Exception $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}
