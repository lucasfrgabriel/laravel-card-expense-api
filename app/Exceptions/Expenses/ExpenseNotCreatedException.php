<?php

namespace App\Exceptions\Expenses;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ExpenseNotCreatedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = $previous->getMessage() ?? 'Ocorreu um erro inesperado ao criar a despesa.';
        parent::__construct($code, $message, $previous);
    }
}
