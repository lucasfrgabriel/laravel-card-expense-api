<?php

namespace App\Exceptions\Cards;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InactiveCardException extends HttpException
{
    public function __construct(int $code = 400, string $message = 'O cartão não está ativo e não pode ser utilizado para novas transações.')
    {
        parent::__construct($code, $message);
    }
}
