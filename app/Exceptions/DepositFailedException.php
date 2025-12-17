<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class DepositFailedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = $previous->getMessage() ?? 'Ocorreu um erro inesperado ao registrar a despesa.';
        parent::__construct($code, $message, $previous);
    }
}
