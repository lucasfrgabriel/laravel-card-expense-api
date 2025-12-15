<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientBalanceException extends HttpException
{
    public function __construct(int $code = 400, string $message = 'Saldo insuficiente.')
    {
        parent::__construct($code, $message);
    }
}
