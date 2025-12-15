<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidAmountException extends HttpException
{
    public function __construct(int $code = 400, string $message = 'O valor de depósito não pode ser menor ou igual a 0')
    {
        parent::__construct($code, $message);
    }
}
