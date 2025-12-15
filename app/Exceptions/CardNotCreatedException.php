<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class CardNotCreatedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = 'Ocorreu um erro inesperado ao criar o cartão.';
        parent::__construct($code, $message, $previous);
    }
}
