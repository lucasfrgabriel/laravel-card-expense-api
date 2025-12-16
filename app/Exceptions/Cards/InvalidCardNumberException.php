<?php

namespace App\Exceptions\Cards;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidCardNumberException extends HttpException
{
    public function __construct(int $code = 400, string $message = 'O número do cartão não é válido.')
    {
        parent::__construct($code, $message);
    }
}
