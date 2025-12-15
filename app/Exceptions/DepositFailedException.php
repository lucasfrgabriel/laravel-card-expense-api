<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DepositFailedException extends Exception
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = 'Ocorreu um erro inesperado ao registrar a despesa.';
        parent::__construct($code, $message, $previous);
    }
}
